define([
    'mage/translate',
    'M2ECore/Plugin/Messages',
], function($t, MessagesObj) {

    window.OnBuyTemplateShipping = Class.create(Common,{

        selectedAccountId: null,
        siteId: null,
        deliveryTemplateId: null,
        urlGetTemplates: '',
        urlGetSites: '',

        initialize: function(config) {
            this.urlGetTemplates = config.urlGetTemplates;
            this.urlGetSites = config.urlGetSites;

            this.setAccountId($('account_id').value);
            console.log(config.siteId);
            this.setSiteId(config.siteId);
            this.setDeliveryTemplateId(config.deliveryTemplateId);

            this.initObservers();
        },

        // ----------------------------------------

        initObservers: function() {
            const self = this;

            $('account_id').observe('change', function() {
                self.setAccountId($('account_id').value || self.selectedAccountId);
            });

            $('site_id').observe('change', function() {
                self.setSiteId($('site_id').value || null);
                self.updateDeliveryTemplates(false);
            });

            jQuery('#handling_time_mode')
                    .on('change', this.handlingTimeChange.bind(this))
                    .trigger('change');
        },

        hasAccountId: function() {
            return this.accountId !== null;
        },

        setAccountId: function(id) {
            this.accountId = parseInt(id) || null;

            if (this.hasAccountId()) {
                this.loadAccountData();
            }
        },

        getAccountId: function() {
            return this.accountId;
        },

        loadAccountData: function() {
            this.updateSites();
        },

        // ----------------------------------------

        hasSiteId: function() {
            return this.siteId !== null;
        },

        setSiteId: function(id) {
            this.siteId = id || null;
            console.log(this.siteId);
            console.log(this.hasSiteId());

            if (this.hasSiteId()) {
                jQuery('#refresh_templates').show();
                jQuery('.actions').show();
            }
        },

        getSiteId: function() {
            return this.siteId;
        },

        setDeliveryTemplateId: function(id) {
            this.deliveryTemplateId = id || null;
        },

        hasDeliveryTemplateId: function() {
            return this.deliveryTemplateId !== null;
        },

        getDeliveryTemplateId: function() {
            return this.deliveryTemplateId;
        },

        // ----------------------------------------

        updateSites: function() {
            const self = this;

            new Ajax.Request(this.urlGetSites, {
                method: 'get',
                parameters: { account_id: self.getAccountId() },
                onSuccess: function(transport) {
                    const response = JSON.parse(transport.responseText);
                    if (response.result) {
                        self.renderSites(response.sites);
                        return;
                    }

                    console.error(response.message);
                },
            });
        },

        renderSites: function(sites) {
            const select = jQuery('#site_id');
            select.find('option').remove();

            select.append(new Option('', ''));
            sites.forEach(function(site) {
                select.append(new Option(site.site_name, site.id));
            });
            if (this.hasSiteId()) {
                select.val(this.getSiteId());
                this.updateDeliveryTemplates(false);
            }
        },

        // ----------------------------------------

        updateDeliveryTemplates: function(isForce) {
            const self = this;

            new Ajax.Request(this.urlGetTemplates, {
                method: 'post',
                parameters: {
                    account_id: self.getAccountId(),
                    site_id: self.getSiteId(),
                    force: isForce ? 1 : 0
                },
                onSuccess: function(transport) {
                    const response = JSON.parse(transport.responseText);
                    if (response.result) {
                        self.renderDeliveryTemplates(
                                response.templates.each(function(template) {
                                    return {
                                        'id': template.id,
                                        'title': template.title,
                                    };
                                }),
                        );

                        return;
                    }

                    console.error(response.message);
                },
            });
        },

        renderDeliveryTemplates: function(deliveryTemplates) {
            const select = jQuery('#delivery_template_id');
            select.find('option').remove();

            deliveryTemplates.each(function(deliveryTemplate) {
                select.append(new Option(deliveryTemplate.title, deliveryTemplate.id));
            });

            if (this.hasDeliveryTemplateId()) {
                select.val(this.getDeliveryTemplateId());
            }
        },

        handlingTimeChange: function (event) {
            const el = event.target;

            if (el.value == OnBuy.php.constant('\\M2E\\OnBuy\\Model\\Policy\\Shipping::HANDLING_TIME_MODE_VALUE')) {
                this.updateHiddenValue(el, jQuery('#handling_time')[0]);
            }

            if (el.value == OnBuy.php.constant('\\M2E\\OnBuy\\Model\\Policy\\Shipping::HANDLING_TIME_MODE_ATTRIBUTE')) {
                this.updateHiddenValue(el, jQuery('#handling_time_attribute')[0]);
            }
        },

        // ----------------------------------------

        submitForm: function(formId, url, messageObj) {
            const form = jQuery('#' + formId);
            if (!form.validation() || !form.validation('isValid')) {
                return false;
            }

            const self = this;

            const formData = form.serialize(true);

            let result = false;
            new Ajax.Request(url, {
                method: 'post',
                asynchronous: false,
                parameters: formData,
                onSuccess: function(transport) {
                    const response = JSON.parse(transport.responseText);

                    if (response.result) {
                        result = true;

                        return;
                    }

                    messageObj.clear();
                    response.messages.each(function(message) {
                        messageObj.addError(message);
                    });
                },
            });

            return result;
        },
    });
});
