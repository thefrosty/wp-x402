<?php

declare(strict_types=1);

namespace TheFrosty\WpX402\Paywall;

use TheFrosty\WpX402\Api\Api;
use TheFrosty\WpX402\Api\Bots;
use TheFrosty\WpX402\Models\PaymentRequired;
use TheFrosty\WpX402\Models\PaymentRequired\Accepts;
use TheFrosty\WpX402\Models\PaymentRequired\UrlResource;
use TheFrosty\WpX402\Networks\Mainnet;
use TheFrosty\WpX402\Networks\Testnet;
use TheFrosty\WpX402\ServiceProvider;
use TheFrosty\WpX402\Settings\Settings;
use TheFrosty\WpX402\Telemetry\EventType;
use WP_Http;
use function array_keys;
use function array_rand;
use function base64_encode;
use function esc_html__;
use function get_permalink;
use function get_post;
use function get_the_date;
use function get_the_title;
use function is_attachment;
use function is_singular;
use function is_wp_error;
use function json_encode;
use function sprintf;
use function status_header;
use function str_word_count;
use function strip_tags;
use function TheFrosty\WpUtilities\exitOrThrow;
use function TheFrosty\WpX402\telemetry;
use function wp_remote_retrieve_body;
use function wp_remote_retrieve_response_code;
use const JSON_THROW_ON_ERROR;

/**
 * Class ForHumans
 * @package TheFrosty\WpX402\Paywall
 */
class ForHumans extends AbstractPaywall
{

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addFilter('the_content', [$this, 'theContent']);
    }

    /**
     * Redirect based on current template conditions.
     * @throws \JsonException
     * @throws \TheFrosty\WpUtilities\Exceptions\TerminationException
     * @throws \Exception
     */
    protected function theContent(string $content): string
    {
        $wallet = Settings::getWallet();
        $validator = $this->getContainer()?->get(ServiceProvider::WALLET_ADDRESS_VALIDATOR);
        if (!Api::isValidWallet($validator, $wallet)) {
            return $content; // @TODO we should look into doing something if a wallet is invalid
        }

        $is_mainnet = Settings::isMainnet();

        $payment_required = new PaymentRequired([
            PaymentRequired::ERROR => esc_html__('PAYMENT-SIGNATURE header is required', 'wp-x402'),
            PaymentRequired::RESOURCE => [
                UrlResource::URL => get_permalink(),
                UrlResource::DESCRIPTION => Entitlement::PAYMENT_REQUIRED->label(),
                UrlResource::MIME_TYPE => 'text/html',
            ],
            PaymentRequired::ACCEPTS => [
                [
                    Accepts::SCHEME => 'exact',
                    Accepts::NETWORK => $is_mainnet ? Mainnet::BASE->value : Testnet::BASE->value,
                    Accepts::AMOUNT => Settings::getPrice(),
                    Accepts::ASSET => $is_mainnet ? Mainnet::ASSET_BASE->value : Testnet::ASSET_BASE->value,
                    Accepts::PAY_TO => $wallet,
                    Accepts::MAX_TIMEOUT_SECONDS => 60,
                    Accepts::EXTRA => [
                        'name' => 'USDC',
                        'version' => 2,
                    ],
                ],
            ],
        ]);
        
        return <<<HTML
            <div style="filter:blur(5px); text-shadow:0 0 2px #000; z-index:-999">$content</div>
            HTML;
    }
}
