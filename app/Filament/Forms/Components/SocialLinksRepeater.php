<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class SocialLinksRepeater extends Field
{
    protected string $view = 'filament.forms.components.social-links-repeater';

    public static function make(?string $name = null): static
    {
        return app(static::class, ['name' => $name]);
    }

    public function getChildComponents(?string $key = null): array
    {
        return [
            Repeater::make($this->getName())
                ->schema([
                    Select::make('platform')
                        ->label(__('settings.social.platform_label'))
                        ->options([
                            'github' => 'GitHub',
                            'linkedin' => 'LinkedIn',
                            'twitter' => 'X (Twitter)',
                            'youtube' => 'YouTube',
                            'facebook' => 'Facebook',
                            'instagram' => 'Instagram',
                            'tiktok' => 'TikTok',
                            'twitch' => 'Twitch',
                            'discord' => 'Discord',
                            'website' => __('settings.social.platform_website'),
                            'portfolio' => __('settings.social.platform_portfolio'),
                            'blog' => __('settings.social.platform_blog'),
                            'other' => __('settings.social.platform_other'),
                        ])
                        ->native(false)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            // Clear the URL when platform changes
                            if ($get('url')) {
                                $set('url', '');
                            }
                        })
                        ->columnSpan(1),

                    TextInput::make('url')
                        ->label(__('settings.social.url_label'))
                        ->url()
                        ->required()
                        ->placeholder(function (callable $get) {
                            return match ($get('platform')) {
                                'github' => 'https://github.com/username',
                                'linkedin' => 'https://linkedin.com/in/username',
                                'twitter' => 'https://x.com/username',
                                'youtube' => 'https://youtube.com/@username',
                                'facebook' => 'https://facebook.com/username',
                                'instagram' => 'https://instagram.com/username',
                                'tiktok' => 'https://tiktok.com/@username',
                                'twitch' => 'https://twitch.tv/username',
                                'discord' => 'https://discord.gg/invite-code',
                                'website' => 'https://example.com',
                                'portfolio' => 'https://portfolio.example.com',
                                'blog' => 'https://blog.example.com',
                                default => 'https://',
                            };
                        })
                        ->helperText(function (callable $get) {
                            return match ($get('platform')) {
                                'github' => __('settings.social.helper.github'),
                                'linkedin' => __('settings.social.helper.linkedin'),
                                'twitter' => __('settings.social.helper.twitter'),
                                'discord' => __('settings.social.helper.discord'),
                                default => null,
                            };
                        })
                        ->prefix(function (callable $get) {
                            return match ($get('platform')) {
                                'github' => '🐙',
                                'linkedin' => '💼',
                                'twitter' => '🐦',
                                'youtube' => '📺',
                                'facebook' => '👥',
                                'instagram' => '📷',
                                'tiktok' => '🎵',
                                'twitch' => '🎮',
                                'discord' => '💬',
                                'website' => '🌐',
                                'portfolio' => '💼',
                                'blog' => '📝',
                                default => '🔗',
                            };
                        })
                        ->columnSpan(1),
                ])
                ->columns(2)
                ->reorderable()
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['platform']
                        ? ucfirst($state['platform']).($state['url'] ? ' - '.parse_url($state['url'], PHP_URL_HOST) : '')
                        : null
                )
                ->addActionLabel(__('settings.social.add_link'))
                ->defaultItems(0)
                ->columnSpanFull(),
        ];
    }
}
