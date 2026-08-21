<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Data\Input\ApiKeyRequest;
use DenLopes\Waha\Data\Input\MessagePoll;
use DenLopes\Waha\Data\Input\Row;
use DenLopes\Waha\Data\Input\Section;
use DenLopes\Waha\Data\Input\SendListMessage;
use PHPUnit\Framework\TestCase;

final class WahaDataSerializationTest extends TestCase
{
    public function test_to_array_recurses_into_nested_dtos(): void
    {
        $message = new SendListMessage(
            title: 'Menu',
            button: 'Choose',
            sections: [
                new Section('Main', [
                    new Row('Option A', 'a'),
                    new Row('Option B', 'b', 'Second choice'),
                ]),
            ],
        );

        $this->assertSame([
            'title'    => 'Menu',
            'button'   => 'Choose',
            'sections' => [
                [
                    'title' => 'Main',
                    'rows'  => [
                        ['title' => 'Option A', 'rowId' => 'a'],
                        ['title' => 'Option B', 'rowId' => 'b', 'description' => 'Second choice'],
                    ],
                ],
            ],
        ], $message->toArray());
    }

    public function test_to_array_skips_nulls_by_default(): void
    {
        $message = new SendListMessage('Menu', 'Choose', []);

        $this->assertArrayNotHasKey('description', $message->toArray());
        $this->assertArrayNotHasKey('footer', $message->toArray());
    }

    public function test_to_array_include_null_preserves_nulls(): void
    {
        $request = new ApiKeyRequest(isAdmin: false, isActive: true, session: null);

        $this->assertArrayHasKey('session', $request->toArray(true));
        $this->assertNull($request->toArray(true)['session']);
    }

    public function test_to_json_round_trips(): void
    {
        $message = new SendListMessage('Menu', 'Choose', [
            new Section('Main', [new Row('Option A', 'a')]),
        ]);

        $decoded = json_decode($message->toJson(), true);

        $this->assertSame($message->toArray(), $decoded);
    }

    public function test_from_json_maps_nested_dtos(): void
    {
        $message = SendListMessage::fromJson(json_encode([
            'title'    => 'Menu',
            'button'   => 'Choose',
            'sections' => [
                ['title' => 'Main', 'rows' => [['title' => 'A', 'rowId' => 'a']]],
            ],
        ], JSON_THROW_ON_ERROR));

        $this->assertInstanceOf(SendListMessage::class, $message);
        $this->assertInstanceOf(Section::class, $message->sections[0]);
        $this->assertInstanceOf(Row::class, $message->sections[0]->rows[0]);
    }

    public function test_message_poll_serializes_bool_and_options(): void
    {
        $poll = new MessagePoll('Vote', ['A', 'B'], true);

        $this->assertSame([
            'name'            => 'Vote',
            'options'         => ['A', 'B'],
            'multipleAnswers' => true,
        ], $poll->toArray());
    }
}
