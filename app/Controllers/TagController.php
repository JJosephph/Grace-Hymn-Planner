<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Tag;

class TagController extends Controller
{
    public function index(): void
    {
        Auth::requireEditor();
        $this->view('tags/index', ['title' => '标签管理', 'tagGroups' => (new Tag())->allGroupsWithTags()]);
    }

    public function storeGroup(): void
    {
        Auth::requireEditor();
        Csrf::verify();
        (new Tag())->createGroup($_POST);
        set_flash('success', '标签组已创建。');
        $this->redirect('/tags');
    }

    public function storeTag(): void
    {
        Auth::requireEditor();
        Csrf::verify();
        (new Tag())->createTag($_POST);
        set_flash('success', '标签已创建。');
        $this->redirect('/tags');
    }
}

