<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Hymn;
use App\Models\ServicePlan;
use App\Models\Tag;
use App\Models\Tune;

class HymnController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),
            'completeness_status' => trim($_GET['completeness_status'] ?? ''),
            'missing_field' => trim($_GET['missing_field'] ?? ''),
            'tune_id' => trim($_GET['tune_id'] ?? ''),
            'difficulty' => trim($_GET['difficulty'] ?? ''),
            'familiarity' => trim($_GET['familiarity'] ?? ''),
            'tag_ids' => $_GET['tag_ids'] ?? [],
        ];

        $this->view('hymns/index', [
            'title' => '圣诗库',
            'hymns' => (new Hymn())->search($filters),
            'filters' => $filters,
            'tagGroups' => (new Tag())->allGroupsWithTags(),
            'tunes' => (new Tune())->options(),
            'latestPlan' => (new ServicePlan())->latest(),
        ]);
    }

    public function create(): void
    {
        Auth::requireEditor();
        $this->formView('hymns/create', ['title' => '新增圣诗', 'hymn' => null, 'selectedTags' => []]);
    }

    public function store(): void
    {
        Auth::requireEditor();
        Csrf::verify();

        try {
            $id = (new Hymn())->create($_POST, $_POST['tag_ids'] ?? []);
            set_flash('success', '圣诗已保存。');
            $next = ($_POST['next'] ?? '') === 'continue' ? '/hymns/' . $id . '/edit' : '/hymns/' . $id;
            $this->redirect($next);
        } catch (\Throwable $exception) {
            set_flash('error', $exception->getMessage());
            $this->redirect('/hymns/create');
        }
    }

    public function show(string $id): void
    {
        $hymn = (new Hymn())->find((int) $id);
        if (!$hymn) {
            http_response_code(404);
            echo 'Hymn not found';
            return;
        }

        $this->view('hymns/show', [
            'title' => $hymn['title_cn'],
            'hymn' => $hymn,
            'latestPlan' => (new ServicePlan())->latest(),
        ]);
    }

    public function edit(string $id): void
    {
        Auth::requireEditor();
        $hymn = (new Hymn())->find((int) $id);
        if (!$hymn) {
            http_response_code(404);
            echo 'Hymn not found';
            return;
        }

        $this->formView('hymns/edit', [
            'title' => '编辑 ' . $hymn['title_cn'],
            'hymn' => $hymn,
            'selectedTags' => array_map('intval', array_column($hymn['tags'], 'id')),
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireEditor();
        Csrf::verify();

        try {
            (new Hymn())->updateHymn((int) $id, $_POST, $_POST['tag_ids'] ?? []);
            set_flash('success', '圣诗已更新。');
        } catch (\Throwable $exception) {
            set_flash('error', $exception->getMessage());
        }

        $this->redirect('/hymns/' . (int) $id . '/edit');
    }

    public function hide(string $id): void
    {
        Auth::requireEditor();
        Csrf::verify();
        (new Hymn())->setStatus((int) $id, 'hidden');
        set_flash('success', '圣诗已隐藏。');
        $this->redirect('/hymns');
    }

    public function delete(string $id): void
    {
        Auth::requireEditor();
        Csrf::verify();
        (new Hymn())->setStatus((int) $id, 'archived');
        set_flash('success', '圣诗已归档。');
        $this->redirect('/hymns');
    }

    private function formView(string $view, array $data): void
    {
        $data['tagGroups'] = (new Tag())->allGroupsWithTags();
        $data['tunes'] = (new Tune())->options();
        $this->view($view, $data);
    }
}
