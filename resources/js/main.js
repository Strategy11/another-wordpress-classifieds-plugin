AWPCP.run('awpcp/main', ['jquery', 'awpcp/jquery-collapsible'], function($) {
    $(function(){
        $('.awpcp-categories-list .top-level-category').closest('li').collapsible();
    });
});
