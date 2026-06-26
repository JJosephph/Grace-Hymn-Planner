<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Hymn;
use App\Models\ServicePlan;
use App\Models\Tag;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $hymns = new Hymn();
        $plans = new ServicePlan();
        $tags = new Tag();

        $this->view('dashboard/index', [
            'title' => '司会工作台',
            'counts' => $hymns->counts(),
            'latestHymns' => $hymns->latest(),
            'incompleteHymns' => $hymns->incomplete(),
            'recentUsedHymns' => $hymns->recentUsed(),
            'latestPlan' => $plans->latest(),
            'tagGroups' => $tags->allGroupsWithTags(),
        ]);
    }

    public function search(): void
    {
        Auth::requireLogin();
        $q = trim($_GET['q'] ?? '');
        $hymns = (new Hymn())->search(['q' => $q]);
        $this->json(['items' => array_slice($hymns, 0, 12)]);
    }
}

