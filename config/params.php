<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'rules' => [
        'minPrize' => 1, // минимальный денежный приз
        'maxPrize' => 500, //макс. денежный приз
        'minPoints' => 1, // минимальный приз с баллами
        'maxPoints' => 1000, // максимальный приз с баллами
        'ratio' => 1.5, // коэфициент конвертации денег в баллы
        'total' => 3000, // изначальная сумма для розыгрыша
        'itemsLimit' => 6 // ограничение разыгрываемых предметов
    ]
];
