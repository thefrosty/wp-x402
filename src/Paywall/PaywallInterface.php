<?php

declare(strict_types=1);

namespace TheFrosty\WpX402\Paywall;

/**
 * Interface PaywallInterface
 * @package TheFrosty\WpX402\Paywall
 */
interface PaywallInterface
{
    public const float DEFAULT_PRICE = 0.01;
    final public const string TESTNET_WALLET = '0x505bc35f0a83c9ed06c6f94e68f0f86cf2812a6b'; // phpcs:ignore
}
