<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Hymn;
use App\Models\ServicePlan;

class ServicePlanController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $this->view('service_plans/index', ['title' => '本周选诗', 'plans' => (new ServicePlan())->all()]);
    }

    public function create(): void
    {
        Auth::requireEditor();
        $defaultDate = date('Y-m-d', strtotime('next sunday'));
        $this->view('service_plans/form', [
            'title' => '新建崇拜计划',
            'plan' => [
                'title' => $defaultDate . ' 主日崇拜',
                'service_date' => $defaultDate,
                'sermon_title' => '',
                'sermon_scripture' => '',
                'sermon_theme' => '',
                'sermon_outline' => '',
                'sermon_keywords' => '',
                'notes' => '',
            ],
        ]);
    }

    public function store(): void
    {
        Auth::requireEditor();
        Csrf::verify();
        $id = (new ServicePlan())->create($_POST);
        set_flash('success', '崇拜计划已创建。');
        $this->redirect('/plans/' . $id);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $plan = (new ServicePlan())->find((int) $id);
        if (!$plan) {
            http_response_code(404);
            echo 'Plan not found';
            return;
        }
        $this->view('service_plans/show', [
            'title' => $plan['title'],
            'plan' => $plan,
            'slotOptions' => ServicePlan::slotOptions(),
            'hymns' => (new Hymn())->search(['status' => 'active']),
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireEditor();
        Csrf::verify();
        (new ServicePlan())->updatePlan((int) $id, $_POST);
        set_flash('success', '崇拜计划已更新。');
        $this->redirect('/plans/' . (int) $id);
    }

    public function addItem(string $id): void
    {
        Auth::requireEditor();
        Csrf::verify();
        (new ServicePlan())->addItem((int) $id, (int) $_POST['hymn_id'], $_POST['slot_type'] ?? 'candidate', $_POST['item_status'] ?? 'candidate', $_POST['note'] ?? '');
        set_flash('success', '诗歌已加入计划。');
        $this->redirect('/plans/' . (int) $id);
    }

    public function updateItem(string $id): void
    {
        Auth::requireEditor();
        Csrf::verify();
        (new ServicePlan())->updateItem((int) $id, $_POST);
        set_flash('success', '诗歌环节已更新。');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/plans');
    }

    public function deleteItem(string $id): void
    {
        Auth::requireEditor();
        Csrf::verify();
        (new ServicePlan())->deleteItem((int) $id);
        set_flash('success', '诗歌已移除。');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/plans');
    }

    public function export(string $id): void
    {
        Auth::requireLogin();
        $planModel = new ServicePlan();
        $plan = $planModel->find((int) $id);
        if (!$plan) {
            http_response_code(404);
            echo 'Plan not found';
            return;
        }

        (new Hymn())->markUsed($planModel->selectedHymnIds((int) $id));

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="service-plan-' . (int) $id . '.txt"');
        echo $plan['title'] . "\n";
        echo '日期：' . $plan['service_date'] . "\n";
        echo '证道：' . $plan['sermon_title'] . "\n";
        echo '经文：' . $plan['sermon_scripture'] . "\n\n";

        foreach (ServicePlan::slotOptions() as $slot => $label) {
            if ($slot === 'candidate') {
                continue;
            }
            echo '[' . $label . "]\n";
            foreach ($plan['items_grouped'][$slot] ?? [] as $item) {
                echo '- ' . $item['title_cn'];
                if (!empty($item['first_line'])) {
                    echo '｜' . $item['first_line'];
                }
                if (!empty($item['note'])) {
                    echo '｜备注：' . $item['note'];
                }
                echo "\n";
            }
            echo "\n";
        }
        exit;
    }
}

