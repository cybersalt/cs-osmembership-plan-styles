<?php

/**
 * @package     Cybersalt.Plugin
 * @subpackage  System.CsOsMembershipPlanStyles
 *
 * @copyright   (C) 2026 Cybersalt. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Cybersalt\Plugin\System\CsOsMembershipPlanStyles\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;

/**
 * Highlights OS Membership directory entries for members on selected plans by
 * injecting a configurable CSS class onto every `<a>` link to those members.
 */
class CsOsMembershipPlanStyles extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterRender' => 'onAfterRender',
        ];
    }

    public function onAfterRender(): void
    {
        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            return;
        }

        $planIds = $this->getConfiguredPlanIds();

        if ($planIds === []) {
            return;
        }

        $body = $app->getBody();

        if ($body === '' || stripos($body, 'view=member') === false) {
            return;
        }

        $memberIds = $this->getMemberIdsForPlans($planIds);

        if ($memberIds === []) {
            return;
        }

        $className = $this->getSanitisedClassName();
        $body      = $this->addClassToMemberLinks($body, $memberIds, $className);

        if ((int) $this->params->get('inject_css', 1) === 1) {
            $body = $this->injectStylesheet($body, $className);
        }

        $app->setBody($body);
    }

    /**
     * Read the configured plan IDs as an array of positive integers.
     */
    private function getConfiguredPlanIds(): array
    {
        $raw = $this->params->get('premium_plan_ids', []);

        if (!is_array($raw)) {
            $raw = $raw === '' || $raw === null ? [] : [$raw];
        }

        $ids = [];

        foreach ($raw as $value) {
            $id = (int) $value;

            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /**
     * Query OS Membership for active, published subscriber IDs on the given plan IDs.
     */
    private function getMemberIdsForPlans(array $planIds): array
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__osmembership_subscribers'))
            ->whereIn($db->quoteName('plan_id'), $planIds, ParameterType::INTEGER)
            ->where($db->quoteName('plan_subscription_status') . ' = 1')
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('plan_main_record') . ' = 1');

        try {
            $db->setQuery($query);
            $rows = $db->loadColumn() ?: [];
        } catch (\Throwable $e) {
            // OS Membership table likely missing; fail closed without crashing the page.
            return [];
        }

        return array_values(array_unique(array_map('intval', $rows)));
    }

    /**
     * Return the configured CSS class name, sanitised to a safe identifier.
     */
    private function getSanitisedClassName(): string
    {
        $raw = trim((string) $this->params->get('css_class', 'cs-osm-premium-member'));

        // Keep only characters that are valid in a CSS class identifier.
        $clean = preg_replace('/[^A-Za-z0-9_\-]/', '', $raw);

        if ($clean === '' || $clean === null) {
            return 'cs-osm-premium-member';
        }

        return $clean;
    }

    /**
     * Inject the class name onto every `<a href="...view=member&id=N...">` for matching N.
     */
    private function addClassToMemberLinks(string $body, array $memberIds, string $className): string
    {
        if ($memberIds === []) {
            return $body;
        }

        $idGroup = implode('|', array_map('intval', $memberIds));
        $pattern = '/<a\b([^>]*?\bhref\s*=\s*"[^"]*\bview=member&(?:amp;)?id=(?:' . $idGroup . ')\b[^"]*"[^>]*)>/i';

        $result = preg_replace_callback(
            $pattern,
            function (array $match) use ($className): string {
                $attrs = $match[1];

                if (preg_match('/\bclass\s*=\s*"([^"]*)"/i', $attrs)) {
                    $attrs = preg_replace_callback(
                        '/\bclass\s*=\s*"([^"]*)"/i',
                        function (array $cm) use ($className): string {
                            $existing = trim($cm[1]);
                            $tokens   = $existing === '' ? [] : preg_split('/\s+/', $existing);

                            if (!in_array($className, $tokens, true)) {
                                $tokens[] = $className;
                            }

                            return 'class="' . implode(' ', $tokens) . '"';
                        },
                        $attrs
                    );
                } else {
                    $attrs .= ' class="' . $className . '"';
                }

                return '<a' . $attrs . '>';
            },
            $body
        );

        return $result ?? $body;
    }

    /**
     * Inject a `<style>` block into the document head with the default bold rule
     * plus any admin-provided custom CSS.
     */
    private function injectStylesheet(string $body, string $className): string
    {
        $customCss  = (string) $this->params->get('custom_css', '');
        $defaultCss = '.' . $className . ' { font-weight: 700; }';

        if ($customCss !== '') {
            // Defense in depth: prevent admin-supplied CSS from closing the
            // <style> tag or sneaking a <script> open. Admins with plugin-edit
            // permission are trusted, but a typo or copy-paste from a hostile
            // snippet shouldn't escalate to script execution.
            $customCss = preg_replace(
                ['#</\s*style#i', '#<\s*script#i'],
                ['<\\/style', '<\\/script'],
                $customCss
            ) ?? '';
        }

        $css = $defaultCss;

        if ($customCss !== '') {
            $css .= "\n" . $customCss;
        }

        $style = '<style id="cs-osmembership-plan-styles">' . "\n" . $css . "\n" . '</style>';

        if (stripos($body, '</head>') !== false) {
            return preg_replace('/<\/head>/i', $style . "\n</head>", $body, 1) ?? $body;
        }

        return $style . $body;
    }
}
