<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\Phrase;

/**
 * Class Admin_message_filter_blacklistController
 *
 * @package app\controllers\bot
 */
class Admin_message_filter_blacklistController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $telegramUser = $this->getTelegramUser();
        $telegramUser->getState()->setName(null);
        $telegramUser->save();

        $chatTitle = $chat->title;
        $phrases = $chat->getBlacklistPhrases();

        $buttons = [];
        foreach ($phrases as $phrase) {
            $buttons[] = [
                [
                    'callback_data' => '/admin_message_filter_phrase ' . $phrase->id,
                    'text' => $phrase->text,
                ],
            ];
        }

        $buttons[] = [
            [
                'callback_data' => '/admin_message_filter ' . $chatId,
                'text' => '🔙',
            ],
            [
                'callback_data' => '/admin_message_filter_newphrase ' . Phrase::TYPE_BLACKLIST . ' ' . $chatId,
                'text' => '➕',
            ],
        ];

        if ($this->getUpdate()->getCallbackQuery()) {
            return [
                new EditMessageTextCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                    $this->render('index', compact('chatTitle')),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup($buttons),
                    ]
                ),
            ];
        } else {
            return [
                new SendMessageCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->render('index', compact('chatTitle')),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup($buttons),
                    ]
                ),
            ];
        }
    }
}