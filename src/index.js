const { registerPlugin } = wp.plugins;
const { __ } = wp.i18n;
const { Fragment } = wp.element;
const { PluginSidebarMoreMenuItem, PluginSidebar } = wp.editPost;
const { PanelBody, PanelRow, ToggleControl } = wp.components;
const { compose } = wp.compose;
const { withDispatch, withSelect } = wp.data;
 
const CustomSidebarMetaComponent = (props) => {
    return(
        <ToggleControl
            label={__('Auto publish to Koo', 'koo')}
            checked={props.customPostMetaValue}
            onChange={props.setCustomPostMeta}
        />
    );
}
 
const CustomSidebarMeta = compose([
    withSelect(select => {
        return { customPostMetaValue: select('core/editor').getEditedPostAttribute('meta')['koo_publish_custom_meta'] }
    }),
    withDispatch(dispatch => {
        return { 
            setCustomPostMeta: function(value) {
                dispatch('core/editor').editPost({ meta: { koo_publish_custom_meta: value } });
            }
        }
    })
])(CustomSidebarMetaComponent);
 
const CustomSidebarComponent = () => {
    return(
        <Fragment>
            <PluginSidebarMoreMenuItem 
                target='koo-custom-sidebar'
                icon='edit large'
            >{__('Koo publisher sidebar', 'koo')}</PluginSidebarMoreMenuItem>
            <PluginSidebar 
                name="koo-custom-sidebar" 
                title={__('Koo publisher sidebar', 'koo')}
            >                
                <PanelBody
                    title={__('Koo publisher section', 'koo')}
                    initialOpen={true}
                >
                    <PanelRow>
                        <CustomSidebarMeta />
                    </PanelRow>
                </PanelBody>
            </PluginSidebar>
        </Fragment>
    );
}
 
registerPlugin('koo-customsidebar', {
    render: CustomSidebarComponent,
    icon: 'edit large'
});