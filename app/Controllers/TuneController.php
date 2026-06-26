<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Tune;

class TuneController extends Controller
{
    public function index(): void
    {
        $this->view('tunes/index', ['title' => '曲调管理', 'tunes' => (new Tune())->all()]);
    }

    public function create(): void
    {
        Auth::requireEditor();
        $this->view('tunes/form', ['title' => '新增曲调', 'tune' => null]);
    }

    public function store(): void
    {
        Auth::requireEditor();
        Csrf::verify();
        $id = (new Tune())->create($_POST);
        set_flash('success', '曲调已保存。');
        $this->redirect('/tunes/' . $id);
    }

    public function show(string $id): void
    {
        $tune = (new Tune())->find((int) $id);
        if (!$tune) {
            http_response_code(404);
            echo 'Tune not found';
            return;
        }
        $this->view('tunes/show', ['title' => $tune['tune_name'], 'tune' => $tune]);
    }

    public function edit(string $id): void
    {
        Auth::requireEditor();
        $tune = (new Tune())->find((int) $id);
        if (!$tune) {
            http_response_code(404);
            echo 'Tune not found';
            return;
        }
        $this->view('tunes/form', ['title' => '编辑曲调', 'tune' => $tune]);
    }

    public function update(string $id): void
    {
        Auth::requireEditor();
        Csrf::verify();
        (new Tune())->update((int) $id, $_POST);
        set_flash('success', '曲调已更新。');
        $this->redirect('/tunes/' . (int) $id);
    }
}
