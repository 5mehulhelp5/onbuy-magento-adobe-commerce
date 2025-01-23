define([
    'M2ECore/Plugin/Messages',
    'OnBuy/Common',
    'Magento_Ui/js/modal/modal'
], function (MessageObj) {

    OrderEditItem = Class.create(Common, {

        // ---------------------------------------

        initialize: function () {
            this.popUp = null;
            this.gridId = null;
            this.orderItemId = null;
        },

        // ---------------------------------------

        openPopUpMappingProduct: function (title, content) {
            const self = this;
            let mappingProductModal = $('mapping_product_modal');

            if (!mappingProductModal) {
                mappingProductModal = new Element('div', {
                    id: 'mapping_product_modal'
                });
            }

            mappingProductModal.innerHTML = '';

            this.mappingProductPopUp = jQuery(mappingProductModal).modal({
                closed: function () {
                    self.reloadGrid();
                    self.orderItemId = null;
                    self.gridId = null;
                    self.mappingProductPopUp = null;
                },
                title: title,
                type: 'slide',
                buttons: []
            });

            this.mappingProductPopUp.modal('openModal');

            mappingProductModal.insert(content);
        },

        openPopUpMappingOptions: function (title, content) {
            const self = this;
            let mappingOptions = $('mapping_product_options');

            if (!mappingOptions) {
                mappingOptions = new Element('div', {
                    id: 'mapping_product_options'
                });
            }

            mappingOptions.innerHTML = '';

            this.mappingOptionsPopUp = jQuery(mappingOptions).modal({
                closed: function () {
                    if (self.mappingProductPopUp) {
                        self.mappingProductPopUp.modal('closeModal');
                        return;
                    }
                    self.reloadGrid();
                    self.orderItemId = null;
                    self.gridId = null;
                    self.mappingOptionsPopUp = null;
                },
                title: title,
                type: 'slide',
                buttons: [{
                    text: OnBuy.translator.translate('Cancel'),
                    click: function () {
                        self.closePopUp();
                    }
                }, {
                    text: OnBuy.translator.translate('Confirm'),
                    class: 'primary',
                    click: function () {
                        self.assignProductDetails();
                    }
                }]
            });

            this.mappingOptionsPopUp.modal('openModal');

            mappingOptions.insert(content);
            self.initMappingOptionsPopUp();
        },

        initMappingOptionsPopUp: function () {
            $$('.form-element').each(function (element) {
                element.observe('change', function () {
                    if (element.selectedIndex != 0) {
                        CommonObj.hideEmptyOption(element);
                    }

                    let hasEmptyOptions = $$('.form-element').any(function (element) {
                        return element.value == ''
                    });
                    if (hasEmptyOptions) {
                        return;
                    }

                    new Ajax.Request(OnBuy.url.get('order/checkProductOptionStockAvailability'), {
                        method: 'get',
                        parameters: Form.serialize('mapping_product_options'),
                        onSuccess: function (transport) {
                            let isInStock = transport.responseText.evalJSON()['is_in_stock'];

                            if (!isInStock) {
                                $('selected_product_option_is_out_of_stock').show();
                            } else {
                                $('selected_product_option_is_out_of_stock').hide();
                            }
                        }
                    });
                });
                element.simulate('change');
            });
        },

        closePopUp: function () {
            if (this.mappingProductPopUp) {
                this.mappingProductPopUp.modal('closeModal');
            }
            if (this.mappingOptionsPopUp) {
                this.mappingOptionsPopUp.modal('closeModal');
            }
        },

        reloadGrid: function () {
            let grid = window[this.gridId + 'JsObject'];

            if (grid) {
                grid.doFilter();
            }
        },

        edit: function (gridId, orderItemId) {
            const self = this;

            self.gridId = gridId;
            self.orderItemId = orderItemId;

            self.getItemEditHtml(orderItemId, function (transport) {
                let response = transport.responseText.evalJSON();

                if (response.error) {
                    if (self.popUp) {
                        self.alert(response.error, function () {
                            self.closePopUp();
                        });
                    } else {
                        MessageObj.addError(response.error);
                    }

                    return;
                }

                let title = response.title;
                let content = response.html;

                if (response.type == OnBuy.php.constant('M2E_OnBuy_Controller_Adminhtml_Order_AssignToMagentoProduct::MAPPING_PRODUCT')) {
                    self.openPopUpMappingProduct(title, content);
                } else if (response.type == OnBuy.php.constant('M2E_OnBuy_Controller_Adminhtml_Order_AssignToMagentoProduct::MAPPING_OPTIONS')) {
                    self.openPopUpMappingOptions(title, content);
                }
            });
        },

        getItemEditHtml: function (orderItemId, callback) {
            new Ajax.Request(OnBuy.url.get('order/assignToMagentoProduct'), {
                method: 'get',
                parameters: {
                    order_item_id: orderItemId
                },
                onSuccess: function (transport) {
                    if (typeof callback == 'function') {
                        callback(transport);
                    }
                }
            });
        },

        afterActionCallback: function (transport) {
            const self = this;
            let response = transport.responseText.evalJSON();

            MessageObj.clear()

            if (response.error) {
                MessageObj.addError(response.error);
                return;
            }

            if (response.continue) {
                self.edit(self.gridId, self.orderItemId);
                return;
            }

            if (response.success) {
                self.closePopUp();
                self.scrollPageToTop();
                MessageObj.addSuccess(response.success);
            }
        },

        // ---------------------------------------

        assignProduct: function (productId) {
            const self = this;
            var productId = +productId || null;

            MessageObj.clear();

            let orderItemId = self.orderItemId;
            if (!orderItemId) {
                return;
            }

            if (productId == null) {
                self.alert(OnBuy.translator.translate('Please enter correct Product ID'));
                return;
            }

            self.confirm({
                actions: {
                    confirm: function () {
                        new Ajax.Request(OnBuy.url.get('order/assignProduct'), {
                            method: 'post',
                            parameters: {
                                product_id: productId,
                                order_item_id: orderItemId
                            },
                            onSuccess: self.afterActionCallback.bind(self)
                        });
                    },
                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        // ---------------------------------------

        assignProductDetails: function () {
            let self = this,
                    confirmAction,
                    validationResult = $$('.form-element').collect(Validation.validate);

            if (validationResult.indexOf(false) != -1) {
                return;
            }

            confirmAction = function () {
                MessageObj.clear();

                new Ajax.Request(OnBuy.url.get('order/assignProductDetails'), {
                    method: 'post',
                    parameters: Form.serialize('mapping_product_options'),
                    onSuccess: self.afterActionCallback.bind(self)
                });
            };

            if ($('save_repair') && $('save_repair').checked) {
                self.confirm({
                    actions: {
                        confirm: function () {
                            confirmAction();
                        },
                        cancel: function () {
                            return false;
                        }
                    }
                });
            } else {
                confirmAction();
            }
        },

        // ---------------------------------------

        unassignProduct: function (gridId, orderItemId) {
            const self = this;

            self.confirm({
                actions: {
                    confirm: function () {
                        self.gridId = gridId;
                        self.orderItemId = orderItemId;

                        new Ajax.Request(OnBuy.url.get('order/unAssignFromMagentoProduct'), {
                            method: 'post',
                            parameters: {
                                order_item_id: orderItemId
                            },
                            onSuccess: function (transport) {
                                self.afterActionCallback(transport);
                                self.reloadGrid();
                                self.gridId = null;
                                self.orderItemId = null;
                            }
                        });
                    },
                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        // ---------------------------------------

        openEditShippingAddressPopup: function (orderId) {
            const self = this;
            let shippingAddressModal = $('shipping_address_modal');

            if (!shippingAddressModal) {
                shippingAddressModal = new Element('div', {
                    id: 'shipping_address_modal'
                });
            }

            shippingAddressModal.innerHTML = '';

            this.editShippingAddressPopUp = jQuery(shippingAddressModal).modal({
                closed: function () {

                },
                title: OnBuy.translator.translate('Edit Shipping Address'),
                type: 'slide',
                buttons: [{
                    text: OnBuy.translator.translate('Cancel'),
                    click: function () {
                        self.editShippingAddressPopUp.modal('closeModal');
                    }
                }, {
                    text: OnBuy.translator.translate('Save'),
                    class: 'primary',
                    click: function () {
                        self.saveShippingAddress();
                    }
                }]
            });

            new Ajax.Request(OnBuy.url.get('getEditShippingAddressForm'), {
                method: 'post',
                parameters: {
                    id: orderId
                },
                onSuccess: function (transport) {
                    shippingAddressModal.insert(transport.responseText);

                    self.initFormValidation('#edit_form');
                    self.editShippingAddressPopUp.modal('openModal');
                }
            });
        },

        // ---------------------------------------

        saveShippingAddress: function () {
            const self = this;

            if (!self.isValidForm()) {
                return;
            }

            new Ajax.Request(OnBuy.url.get('saveShippingAddress'), {
                method: 'post',
                parameters: Form.serialize($('edit_form')),
                onSuccess: function (transport) {
                    let result = transport.responseText.evalJSON();

                    if (result.success) {
                        $('shipping_address_container').update(result.html);
                        self.editShippingAddressPopUp.modal('closeModal');
                    }
                }
            });
        },
    });
});
