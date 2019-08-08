<?php /** module/Requests/config/template_map.php */
return [
    'interpreters-office/requests/index/index' => __DIR__ . '/../view/index/index.phtml',
    'interpreters-office/requests/index/list' => __DIR__ . '/../view/index/list.phtml',
    'interpreters-office/requests/write/create' => __DIR__ . '/../view/index/form.phtml',
    'interpreters-office/requests/write/update' => __DIR__ . '/../view/index/form.phtml',
    'interpreters-office/requests/index/search' => __DIR__ . '/../view/index/search.phtml',
    'interpreters-office/requests/index/view' => __DIR__ . '/../view/index/view.phtml',
    'interpreters-office/requests/index/help' => __DIR__ . '/../view/index/help.phtml',
    'denied' =>  __DIR__ . '/../view/index/denied.phtml',
    'confirm-cancel' =>  __DIR__ . '/../view/index/confirm-cancel.phtml',

    // admin
    'interpreters-office/requests/admin/index/index' => __DIR__ . '/../view/admin/index.phtml',
    'interpreters-office/requests/admin/index/config' => __DIR__ . '/../view/admin/config.phtml',
    'interpreters-office/requests/admin/index/view' => __DIR__ . '/../view/admin/view.phtml',
    'interpreters-office/requests/admin/index/email-modal' => __DIR__ . '/../view/admin/email-modal.phtml',

    // shared
    'partials/request-details' => __DIR__ . '/../view/partials/request-details.phtml',

];
