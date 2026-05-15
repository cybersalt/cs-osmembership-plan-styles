<?php

/**
 * @package     Cybersalt.Plugin
 * @subpackage  System.CsOsMembershipPlanStyles
 *
 * @copyright   (C) 2026 Cybersalt. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            InstallerScriptInterface::class,
            new class () implements InstallerScriptInterface {
                public function install(InstallerAdapter $adapter): bool
                {
                    return true;
                }

                public function update(InstallerAdapter $adapter): bool
                {
                    return true;
                }

                public function uninstall(InstallerAdapter $adapter): bool
                {
                    return true;
                }

                public function preflight(string $type, InstallerAdapter $adapter): bool
                {
                    return true;
                }

                public function postflight(string $type, InstallerAdapter $adapter): bool
                {
                    if (!in_array($type, ['install', 'update', 'discover_install'], true)) {
                        return true;
                    }

                    $isUpdate = $type === 'update';
                    $this->renderInstallCard($isUpdate);

                    return true;
                }

                private function renderInstallCard(bool $isUpdate): void
                {
                    $bodyKey = $isUpdate
                        ? 'PLG_SYSTEM_CSOSMEMBERSHIPPLANSTYLES_POSTINSTALL_UPDATED'
                        : 'PLG_SYSTEM_CSOSMEMBERSHIPPLANSTYLES_POSTINSTALL_INSTALLED';

                    $title       = $this->escape(Text::_('PLG_SYSTEM_CSOSMEMBERSHIPPLANSTYLES_POSTINSTALL_TITLE'));
                    $body        = Text::_($bodyKey);
                    $btnSettings = $this->escape(Text::_('PLG_SYSTEM_CSOSMEMBERSHIPPLANSTYLES_POSTINSTALL_OPEN_SETTINGS'));
                    $btnPlugins  = $this->escape(Text::_('PLG_SYSTEM_CSOSMEMBERSHIPPLANSTYLES_POSTINSTALL_OPEN_PLUGINS'));
                    $footer      = Text::_('PLG_SYSTEM_CSOSMEMBERSHIPPLANSTYLES_POSTINSTALL_FOOTER_BY');

                    $base        = Uri::root();
                    $settingsUrl = $this->escape($base . 'administrator/index.php?option=com_plugins&filter_folder=system&filter_search=csosmembershipplanstyles');
                    $pluginsUrl  = $this->escape($base . 'administrator/index.php?option=com_plugins');

                    echo <<<HTML
<style>
.cs-install-card {
    --cs-header-bg: #fff;
    --cs-header-title: #0102E1;
    --cs-header-border: rgba(0, 0, 0, 0.1);
    border: 1px solid var(--cs-header-border);
    border-radius: 8px;
    margin: 1.25rem 0;
    overflow: hidden;
    font-family: inherit;
}
html[data-bs-theme="dark"] .cs-install-card,
html[data-color-scheme="dark"] .cs-install-card {
    --cs-header-bg: #1f2937;
    --cs-header-title: #FE9904;
    --cs-header-border: rgba(255, 255, 255, 0.1);
}
.cs-install-card .cs-install-header {
    background: var(--cs-header-bg);
    border-bottom: 1px solid var(--cs-header-border);
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.cs-install-card .cs-install-header h3 {
    margin: 0;
    color: var(--cs-header-title);
    font-size: 1.25rem;
    font-weight: 700;
}
.cs-install-card .cs-install-body {
    padding: 1.25rem;
}
.cs-install-card .cs-install-body p {
    margin: 0 0 1rem 0;
    line-height: 1.5;
}
.cs-install-card .cs-install-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.cs-install-card a.cs-cybersalt-btn {
    display: inline-block;
    background-color: #dc6b1a !important;
    color: #fff !important;
    border: 1px solid #dc6b1a !important;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
}
.cs-install-card a.cs-cybersalt-btn:hover,
.cs-install-card a.cs-cybersalt-btn:focus {
    background-color: #b85610 !important;
    border-color: #b85610 !important;
    color: #fff !important;
    text-decoration: none;
}
.cs-install-card .cs-install-footer {
    border-top: 1px solid var(--cs-header-border);
    padding: 0.75rem 1.25rem;
    font-size: 0.875rem;
    opacity: 0.85;
}
</style>
<div class="cs-install-card">
    <div class="cs-install-header">
        <h3>{$title}</h3>
    </div>
    <div class="cs-install-body">
        <p>{$body}</p>
        <div class="cs-install-actions">
            <a class="cs-cybersalt-btn" href="{$settingsUrl}">{$btnSettings}</a>
            <a class="cs-cybersalt-btn" href="{$pluginsUrl}">{$btnPlugins}</a>
        </div>
    </div>
    <div class="cs-install-footer">
        {$footer}
    </div>
</div>
HTML;
                }

                private function escape(string $s): string
                {
                    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
            }
        );
    }
};
