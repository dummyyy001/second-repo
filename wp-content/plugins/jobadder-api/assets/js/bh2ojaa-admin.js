jQuery(document).ready(function($) {
	// Authorization Process
    var authBtn = $("#bh2ojaa_options_auth")
    authBtn.on("click", (e) => {
    	e.preventDefault()
        
        if (!$("#bh2ojaa_options_auth + .loading").length) {
        	authBtn.after('<span class="loading" style="height: 30px; display: inline-flex; padding: 5px; box-sizing: border-box;"><img src="'+wp_obj.admin_images+'/spinner.gif"></span>')
        }
        
        $.ajax({
        	type: "post",
            dataType: "json",
            url: wp_obj.ajax_url,
            data: {
            	action: "get_jobadder_api_auth_ready_url"
            },
            success: (res) => {
                window.location.replace(res)
                if ($("#bh2ojaa_options_auth + .loading").length) {
                    $("#bh2ojaa_options_auth + .loading").remove()
                }
            },
            error: (err) => {
            	console.log(err)
            }
        })
    })
})