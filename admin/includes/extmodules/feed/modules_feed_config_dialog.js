Toc.feed.Feed = function (config) {
    config = config || {};
    config.id = 'feed-win';
    config.title = 'feed';
    config.description = 'description';
    config.labelWidth = 150;
    config.monitorValid = true;
    config.defaults = {
        allowBlank: false,
        labelStyle: 'font-size: 17px',
        anchor: '97%'
    };
    config.items = this.buildForm(200);
    config.buttons = [
        {
            text: 'Connect',
            handler: function(){

                this.moduleForm.form.submit({
                    waitMsg: 'Connecting ...',

                    success: function(form, action){
                        Ext.Msg.alert(action.result.FORM_CODE[0], action.result.FORM_CODE[1]);
                    },
                    failure: function(form, action){
                        Ext.Msg.alert(action.result.FORM_CODE[0], action.result.FORM_CODE[1]);
                    }
                });
            },
            scope:this
        },{
            text: TocLanguage.btnClose,
            handler: function(){
                this.close();
            },
            scope:this
        }
    ];
    this.addEvents({'Connect' : true});
    Toc.feed.Feed.superclass.constructor.call(this, config);
};


Ext.extend(Toc.feed.Feed, Ext.Window, {
    buildForm: function(code) {
        this.moduleForm = new Ext.form.FormPanel({
            url: Toc.CONF.CONN_URL,
            border: false,
            navigation: true,
            baseParams: {
                method: 'post',
                module: 'feed',
                action: 'load_feed'
            }
        });
        this.requestForm(200);
        return this.moduleForm;
    },

    requestForm: function(code) {
        Ext.Ajax.request({
            url: Toc.CONF.CONN_URL,
            params: {
                module: 'feed',
                action: 'load_feed',
                code: code
            },
            callback: function (options, success, response) {
                this.result = Ext.decode(response.responseText);
                this.moduleForm.add(
                    new Ext.form.TextField ({fieldLabel: 'Username', id: 'FEED_USER', value: this.result.FEED_USER}),
                    new Ext.form.TextField ({fieldLabel: 'Password', id: 'FEED_PASSWORD', inputType: 'password', value: this.result.FEED_PASSWORD}),
                    new Ext.form.TextField ({fieldLabel: 'Secret'  , id: 'FEED_SECRET', value: this.result.FEED_SECRET}),
                    new Ext.form.ComboBox({
                        id: 'FEED_TAX_ZONE2',
                        hiddenName: 'FEED_TAX_ZONE',
                        fieldLabel: 'Tax Zone',
                        editable : false,
                        valueField: 'id',
                        forceSelection : true,
                        mode : 'local',
                        triggerAction : 'all',
                        store : new Ext.data.JsonStore({
                            fields : ['id', 'name'],
                            data : this.result.ZONES
                        }),
                        queryMode: 'local',
                        displayField: 'name'
                    }),
                    new Ext.form.ComboBox({
                        id: 'FEED_SHIPPING_METHOD2',
                        hiddenName: 'FEED_SHIPPING_METHOD',
                        fieldLabel: 'Shipping Method',
                        editable : false,
                        valueField: 'name',
                        forceSelection : true,
                        mode : 'local',
                        triggerAction : 'all',
                        store : new Ext.data.JsonStore({
                            fields : ['id', 'name'],
                            data : this.result.SHIPPING_MODULES
                        }),
                        queryMode: 'local',
                        displayField: 'name'
                    }),
                    {xtype: 'box', autoEl: {cn: '<br><h1>• Delivery Time: </h1>'}},
                    new Ext.form.NumberField({fieldLabel: 'From', inputType: 'numberfield', id: 'FEED_DTIME_FROM', value: this.result.FEED_DTIME_FROM }),
                    new Ext.form.NumberField({fieldLabel: 'To', inputType: 'numberfield', id: 'FEED_DTIME_TO', value: this.result.FEED_DTIME_TO}),
                    new Ext.form.ComboBox({
                        id: 'FEED_DTIME_TYPE2',
                        //allowBlank: false,
                        hiddenName: 'FEED_DTIME_TYPE',
                        fieldLabel: 'Type',
                        editable : false,
                        forceSelection : true,
                        mode : 'local',
                        triggerAction : 'all',
                        store : new Ext.data.JsonStore({
                            fields : ['id', 'name'],
                            data : [
                                {id: 'D', name: 'days'},
                                {id: 'W', name: 'weeks'},
                                {id: 'M', name: 'months'}
                            ]
                        }),
                        queryMode: 'local',
                        valueField: 'id',
                        displayField: 'name'
                    }),
                    {xtype: 'box', autoEl: {cn: '<br><h1>• Tracking Pixel: </h1>'}},
                    new Ext.form.NumberField({fieldLabel: 'Client Id', inputType: 'numberfield', id: 'FEED_CLIENT_NUMBER', value: this.result.FEED_CLIENT_NUMBER}),
                    new Ext.form.Checkbox({
                        id: 'FEED_TP_STATE2',
                        fieldLabel: 'Tracking pixel',
                        uncheckedValue: 'N',
                        boxLabel: 'Enable',
                        name: 'FEED_TP_STATE',
                        inputValue: 'Y'
                    })
                );
                this.doLayout();
            }, scope: this
        });
        return Ext.Ajax.request.success;
    }
});