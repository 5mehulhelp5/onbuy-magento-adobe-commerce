<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model;

class TranslateText
{
    public static function getAccountDelete(): string
    {
        return (string)__(
            '<p>You are about to delete your OnBuy seller account from M2E OnBuy Connect. This will remove the
account-related Listings and Products from the extension and disconnect the synchronization.
Your listings on the channel will <b>not</b> be affected.</p>
<p>Please confirm if you would like to delete the account.</p>
<p>Note: once the account is no longer connected to your M2E OnBuy Connect, please remember to delete it from
<a href="%href">M2E Accounts</a></p>',
            ['href' => \M2E\Core\Helper\Module\Support::ACCOUNTS_URL]
        );
    }
}
