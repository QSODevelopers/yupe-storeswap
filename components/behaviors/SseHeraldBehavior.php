<?php

/**
 * Поведение для контроллеров аботаюхи с SSE
 */
class SseHeraldBehavior extends CBehavior
{

    const CONTEXT_INFO = 'info';
    const CONTEXT_SUCCESS = 'success';
    const CONTEXT_WARNING = 'warning';
    const CONTEXT_DANGER = 'danger';

    /**
     * Функция для отправки в буфер произвольного сообщения или события
     * @param  integer $id      id
     * @param  string $message  текст события
     * @param  string $event    произвольное событие
     */
    public function sendSSEMessage($title ='', $message = '', $context = self::CONTEXT_INFO, $event = 'message'){
        echo "event: $event\n";
        echo "id: ".time()."\n";
        $data = CJSON::encode(['title'=> $title, 'message'=>$message, 'context'=>$context]);
        echo "data: $data\n\n";
        //TODO ->>>>>>>>>>>>>>>>>
        Yii::log('Пишу пишу', 'info', 'sse.Zad');
        // sleep(1);
        ob_flush();
        flush();
    }
}