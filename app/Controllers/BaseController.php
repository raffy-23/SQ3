<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $helpers = ['form', 'url', 'security'];
    protected ?array $authUser = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->authUser = current_user();
    }

    protected function render(string $view, array $data = [], string $layout = 'app'): string
    {
        $pageData = array_merge([
            'appName'     => 'SideQuest',
            'authUser'    => $this->authUser,
            'appearance'  => appearance_mode(),
            'pageTitle'   => 'SideQuest',
            'currentPath' => trim(service('uri')->getPath(), '/'),
        ], $data);

        return view('layouts/' . $layout, array_merge($pageData, [
            'contentView' => $view,
            'pageData'    => $pageData,
        ]));
    }
}
