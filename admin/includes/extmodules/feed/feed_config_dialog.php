<?php

require ('feed_data_for_filling_window.php');
$config = new FeedDialog();

$middle = "{xtype: 'box', autoEl: {cn: '   <h1 style=\"color: #000000;  \">Special Delivery Prices</h1>'+
              '<table>' +" ;

foreach($config->attributesExtra as $key=>$value){
    $middle .= "
                    '<tr>' +
                    '    <td>' +
                    '       ".$value."' +
                    '    </td>'+
                    '   <td style=\"width: 20px;\">&nbsp;</td>'+
                    '   <td>' +
                    '       <select style=\"width: 170px;\" name=\"FEED_".$key."_1\">' +
                                 ".$config->getProductTableItems('FEED_'.$key.'_1')."
                    '       </select>' +
                    '   </td>' +
                    '   <td> &nbsp; or &nbsp; </td>'+
                    '   <td>' +
                    '       <input type=\"text\" name=\"FEED_".$key."_2\" style=\"width: 130px;\" value=\"".$config->feedConfig['FEED_'.$key.'_2']." \" />' +
                    '   </td>' +
                    '</tr>' +
    ";
}
$middle .="' </table>'}},
 {text: 'separator above'},";

$middle .= "{xtype: 'box', autoEl: {cn: '   <h1 style=\"color: #000000;  \">Export Configurations</h1>'+'<table>' +" ;
foreach ($config->exportConfig as $key=>$element) {
    $middle .= "

        '<tr>' +
        '   <td>' +
        '      ".$element." '+
        '   </td>'+
        '   <td style=\"width: 20px;\"> &nbsp;</td>'+
        '   <td>' +
        '       <select style=\"width: 170px;\" name=\"FEED_".$key."_1\">' +
                    ".$config->getProductTableItems('FEED_'.$key.'_1')."
        '       </select>' +
        '   </td>' +
        '   <td> &nbsp; or &nbsp; </td>'+
        '   <td>' +
        '       <select style=\"width: 155px;\" name=\"FEED_".$key."_2\">' +
                    ".$config->getProductsAttributesItems('FEED_'.$key.'_2')."
        '       </select>' +
        '   </td>' +
        '   <td> &nbsp; or &nbsp; </td>'+
        '   <td>' +
                    '<input type=\"text\" name=\"FEED_".$key."_3\" style=\"width: 130px;\" value=\"".$config->feedConfig['FEED_'.$key.'_3']." \" />' +
        '   </td>' +
        '</tr>' +";
}

$middle .= "

        '<tr>' +
        '   <td>' +
        '     ••• Packet Size '+
        '   </td>'+
        '   <td style=\"width: 20px;\"> &nbsp;</td>'+
        '   <td>' +
        '       <select style=\"width: 170px;\" name=\"FEED_PACKET_SIZE_1\">' +
                    ".$config->getProductTableItems('FEED_PACKET_SIZE_1')."
        '       </select>' +
        '   </td>' +
        '   <td> &nbsp; or &nbsp; </td>'+
        '   <td>' +
        '       <select style=\"width: 155px;\" name=\"FEED_PACKET_SIZE_2\">' +
                    ".$config->getProductsAttributesItems('FEED_PACKET_SIZE_2')."
        '       </select>' +
        '   </td>' +
        '   <td> &nbsp; or &nbsp; </td>'+
        '</tr>' +

        '<tr>'+
        '   <td style=\"text-align: right ;\">' +
        '     width '+
        '   </td>'+
        '   <td style=\"width: 20px;\"> &nbsp;</td>'+
        '   <td>'+
        '     <input type=\"text\" name=\"FEED_PACKET_SIZE_WIDTH\" style=\"width: 168px;\" value=\"".$config->feedConfig['FEED_PACKET_SIZE_WIDTH']." \" />' +
        '   </td>'+
        '   <td> &nbsp; cm &nbsp; </td>'+
        '</tr>'+

        '<tr>'+
        '   <td style=\"text-align: right ;\">' +
        '     height '+
        '   </td>'+
        '   <td style=\"width: 20px;\"> &nbsp;</td>'+
        '   <td>'+
            '     <input type=\"text\" name=\"FEED_PACKET_SIZE_HEIGHT\" style=\"width: 168px;\" value=\"".$config->feedConfig['FEED_PACKET_SIZE_HEIGHT']." \" />' +
        '   </td>'+
        '   <td> &nbsp; cm &nbsp; </td>'+
        '</tr>'+

        '<tr>'+
        '   <td style=\"text-align: right ;\">' +
        '     length '+
        '   </td>'+
        '   <td style=\"width: 20px;\"> &nbsp;</td>'+
        '   <td>'+
        '     <input type=\"text\" name=\"FEED_PACKET_SIZE_LENGTH\" style=\"width: 168px;\" value=\"".$config->feedConfig['FEED_PACKET_SIZE_LENGTH']." \" />' +
        '   </td>'+
        '   <td> &nbsp; cm &nbsp; </td>'+
        '</tr>'+
        '<tr>'+
        '   <td>'+
        ' <br>'+
        '   </td>'+
        '</tr>'+



        '<tr>'+
        '   <td >' +
        '     Comment '+
        '   </td>'+
        '   <td style=\"width: 20px;\"> &nbsp;</td>'+
        '   <td>'+
        '     <textarea   name=\"FEED_COMMENT\" style=\"width: 168px; height: 100px; border: 1px solid #6D776B; \"  /> ' +
        '        ".$config->feedConfig["FEED_COMMENT"]." '+
        '     </textarea>'+
        '   </td>'+
        '</tr>'+

";

$middle .="' </table>'}},

 {text: 'separator above'},";

$middle .= "{xtype: 'box', autoEl: {cn: '   <h1 style=\"color: #000000;  \">Attributes Extra</h1>'+
              '<table>' +" ;

$middle .= "
                    '   <tr>' +
                    '       <td>' +
                    '          Condition' +
                    '       </td>'+
                    '      <td style=\"width: 157px;\">&nbsp;</td>'+
                    '      <td>' +
                    '          <select style=\"width: 170px;\" name=\"FEED_CONDITION_1\">' +
                    '              <option value=\"N\"  ".$config->getElement('--empty--' )." > --empty-- </option>' +
                    '              <option ".$config->getElement('new')." >          new    </option>' +
                    '              <option ".$config->getElement('used')." >        used    </option>' +
                    '              <option ".$config->getElement('returned')." >  returned  </option>' +
                    '          </select>' +
                    '      </td>' +
                    '      <td> &nbsp; or &nbsp; </td>'+
                    '      <td>' +
                    '          <input type=\"text\" name=\"FEED_CONDITION_2\" style=\"width: 152px;\" value=\"".$config->getValue('FEED_CONDITION_2')." \" />' +
                    '      </td>' +
                    '   </tr>' +
    ";
$middle .="' </table>'}},
 {text: 'separator above'},";

$header =  "
    Toc.feed.Feed = function (config) {
    config = config || {};

    config.id = 'feed-win';
    config.title = 'feed';
    config.padding  = 30;
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
        'Ext.container.Container',
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
                    {xtype: 'box', autoEl: {cn: '<br><h1>• Tracking Pixel: </h1>'}},
                    new Ext.form.NumberField({fieldLabel: 'Client Id', inputType: 'numberfield', id: 'FEED_CLIENT_NUMBER', value: this.result.FEED_CLIENT_NUMBER}),

                    new Ext.form.Checkbox({
                        id: 'FEED_TRACKING_PIXEL_STATUS',
                        fieldLabel: 'Tracking pixel',
                        uncheckedValue: 'false',
                        boxLabel: 'Enable',
                        name: 'FEED_TRACKING_PIXEL_STATUS',
                        inputValue: 'Y',
                        checked : this.result.FEED_TRACKING_PIXEL_STATUS
                    }),
                    new Ext.form.ComboBox({
                        id: 'FEED_TAX_ZONE2',
                        hiddenName: 'FEED_TAX_ZONE',
                        fieldLabel: 'Tax Zone',
                        editable : false,
                        value: this.result.FEED_TAX_ZONE ,
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
                        value: this.result.SHIPPING_MODULES[0].name,
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
                    {text: 'separator above'},
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
                        value: this.result.FEED_DTIME_TYPE,
                        valueField: 'name',
                        displayField: 'name'
                    }),{
                       text: 'separator above'
                    },

";

$footer = "

                 {text: 'separator above'}


                );

                this.doLayout();

            }, scope: this
        });

        return Ext.Ajax.request.success;
    }

});";

echo $header.$middle.$footer ;