define([
    'underscore',
    'mage/translate',
], function(_, $t) {

    window.OnBuyListingCreateGeneral = Class.create({

        accounts: null,
        selectedAccountId: null,
        urlListingCreate: '',
        urlAccountList: '',
        urlSiteList: '',

        // ---------------------------------------

        initialize: function(urlListingCreate, urlAccountList, urlSiteList) {
            this.urlListingCreate = urlListingCreate;
            this.urlAccountList = urlAccountList;
            this.urlSiteList = urlSiteList;

            CommonObj.setValidationCheckRepetitionValue(
                    'OnBuy-listing-title',
                    $t('The specified Title is already used for other Listing. Listing Title must be unique.'),
                    'Listing', 'title', 'id', null,
            );

            this.initAccount();
            this.initNextStep();
        },

        initNextStep: function () {
            const self = this;
            $$('.next_step_button').each(function (btn) {
                btn.observe('click', function () {
                    if (jQuery('#edit_form').valid()) {
                        CommonObj.saveClick(self.urlListingCreate, true);
                    }
                });
            });
        },

        initAccount: function() {
            const self = this;

            $('account_id').observe('change', function() {
                self.selectedAccountId = $('account_id').value || self.selectedAccountId;

                if (_.isNull(self.selectedAccountId)) {
                    return;
                }

                new Ajax.Request(self.urlSiteList, {
                    method: 'get',
                    parameters: {account_id: self.selectedAccountId},
                    onSuccess: function(transport) {
                        const response = JSON.parse(transport.responseText);
                        if (response.result) {
                            self.refreshSites(response.sites);
                            return;
                        }

                        throw response.message;
                    },
                });
            });

            self.renderAccounts();
        },

        refreshSites: function(sites) {
            const select = jQuery('#site_id');
            const selectedSiteId = +select.val();
            select.find('option').remove();

            sites.each(function(site) {
                select.append(new Option(site.country_code, site.id));

                if (selectedSiteId === site.id) {
                    select.val(selectedSiteId);
                }
            });
        },

        renderAccounts: function(callback) {
            const self = this;

            const accountAddBtn = $('add_account');
            const accountLabelEl = $('account_label');
            const accountSelectEl = $('account_id');
            const siteSelectField = $('site_id').up('.field');

            new Ajax.Request(this.urlAccountList, {
                method: 'get',
                onSuccess: function(transport) {
                    const response = transport.responseText.evalJSON();

                    const accounts = response.accounts;

                    if (_.isNull(self.accounts)) {
                        self.accounts = accounts;
                    }

                    if (_.isNull(self.selectedAccountId)) {
                        self.selectedAccountId = $('account_id').value;
                    }

                    const isAccountsChanged = !self.isAccountsEqual(accounts);

                    if (isAccountsChanged) {
                        self.accounts = accounts;
                    }

                    if (accounts.length === 0) {
                        accountAddBtn.down('span').update($t('Add'));
                        accountLabelEl.update($t('Account not found, please create it.'));
                        accountLabelEl.show();
                        accountSelectEl.hide();
                        siteSelectField.hide();
                        return;
                    }

                    accountSelectEl.update();
                    accountSelectEl.appendChild(new Element('option', {style: 'display: none'}));
                    accounts.each(function(account) {
                        accountSelectEl.appendChild(new Element('option', {value: account.id})).insert(account.title);
                    });

                    accountAddBtn.down('span').update($t('Add Another'));

                    if (accounts.length === 1) {
                        const account = _.first(accounts);

                        $('account_id').value = account.id;
                        self.selectedAccountId = account.id;

                        let accountElement;

                        if (OnBuy.formData.wizard) {
                            accountElement = new Element('span').update(account.title);
                        } else {
                            const accountLink = OnBuy.url.get('account/edit', {
                                'id': account.id,
                                close_on_save: 1,
                            });
                            accountElement = new Element('a', {
                                'href': accountLink,
                                'target': '_blank',
                            }).update(account.title);
                        }

                        accountLabelEl.update(accountElement);

                        accountLabelEl.show();
                        accountSelectEl.dispatchEvent(new Event('change'));
                        accountSelectEl.hide();
                        siteSelectField.show();
                    } else if (isAccountsChanged) {
                        self.selectedAccountId = _.last(accounts).id;

                        accountLabelEl.hide();
                        accountSelectEl.show();
                        accountSelectEl.dispatchEvent(new Event('change'));
                        siteSelectField.show();
                    }

                    accountSelectEl.setValue(self.selectedAccountId);

                    callback && callback();
                },
            });
        },

        isAccountsEqual: function(newAccounts) {
            if (!newAccounts.length && !this.accounts.length) {
                return true;
            }

            if (newAccounts.length !== this.accounts.length) {
                return false;
            }

            return _.every(this.accounts, function(account) {
                return _.where(newAccounts, account).length > 0;
            });
        },
    });
});
