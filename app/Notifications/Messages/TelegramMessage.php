<?php

namespace App\Notifications\Messages;

class TelegramMessage
{
    protected array $lines = [];

    protected ?string $content = null;

    protected string $parseMode = 'HTML';

    protected bool $disableWebPagePreview = true;

    protected array $extra = [];

    public static function make(): self
    {
        return new self();
    }

    public function line(string $line): self
    {
        $this->lines[] = $line;

        return $this;
    }

    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function parseMode(string $parseMode): self
    {
        $this->parseMode = $parseMode;

        return $this;
    }

    public function disableWebPagePreview(bool $disable = true): self
    {
        $this->disableWebPagePreview = $disable;

        return $this;
    }

    public function with(array $extra): self
    {
        $this->extra = array_merge($this->extra, $extra);

        return $this;
    }

    public function toArray(): array
    {
        $text = $this->content ?? implode("\n", $this->lines);

        return array_filter([
            'text' => $text,
            'parse_mode' => $this->parseMode,
            'disable_web_page_preview' => $this->disableWebPagePreview,
        ] + $this->extra, static fn ($value) => ! is_null($value));
    }
}
