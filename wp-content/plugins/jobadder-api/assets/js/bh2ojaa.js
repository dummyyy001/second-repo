jQuery(document).ready(function($) {
	// Apply for a job
    var applyBtn = $(".job-apply-submit")
    var jobApplyForm = $(".job-apply-form")
    jobApplyForm.on("submit", function(e) {
        e.preventDefault()

        if (!$(".job-apply-submit + .loading").length) {
            applyBtn.after('<span class="loading ml-2 ms-2" style="height: 30px; display: inline-block; padding: 4px; box-sizing: border-box;"><img 30px" src="'+wp_obj.admin_images+'/spinner.gif"></span>')
        }

        // var formData = Object.fromEntries(new FormData(e.target).entries())

        // if (formData.hasOwnProperty("resume")) {
        //     var reader  = new FileReader()
        //     var file    = formData["resume"]
        //     reader.onload = function(e) {
        //         // binary data
        //         formData["resume"] = e.target.result
        //     }
        //     reader.onerror = function(e) {
        //         // error occurred
        //         console.log('Error : ' + e.type)
        //     }
        //     reader.readAsBinaryString(file)
        // }

        // if (formData.hasOwnProperty("cover-letter")) {
        //     var reader  = new FileReader()
        //     var file    = formData["cover-letter"]
        //     reader.onload = function(e) {
        //         // binary data
        //         formData["cover-letter"] = e.target.result
        //     }
        //     reader.onerror = function(e) {
        //         // error occurred
        //         console.log('Error : ' + e.type)
        //     }
        //     reader.readAsBinaryString(file)
        // }

        var messageElement = jobApplyForm.find(".form-message");
        
        var fileInputElement = document.getElementById("file");
  		//var fileName = fileInputElement.files[0].name;
        var fileName = $('#file').val();
        if(fileName){
            

            var formData    = new FormData(e.target);
            //console.log(files);
            //formData.append("data", JSON.stringify(formData));
            formData.append("action", "jobadder_api_apply_for_job");
            jQuery.ajax({
                url:wp_obj.ajax_url,
                type:"POST",
                processData: false,
                contentType: false,
                dataType: 'json',
                enctype: 'multipart/form-data',
                cache: false,
                data: formData,
                success: (res) => {
                    //var messageElement = jobApplyForm.find(".form-message")

                    if (res.success) {
                        if (messageElement.hasClass('text-danger')) {
                            messageElement.removeClass("text-danger")
                        }
                        messageElement.addClass("text-success");
                        messageElement.html("Application submitted!");
                    } else {
                        messageElement.addClass("text-danger")
                        jobApplyForm.find(".form-message").html(res.data.message)
                    }

                    if ($(".job-apply-submit + .loading").length) {
                        $(".job-apply-submit + .loading").remove()
                    }
                },
                error: (err) => {
                    console.log(err)
                }
            });
        }
        else{
            setTimeout(function() { 
                if (messageElement.hasClass('text-success')) {
                    messageElement.removeClass("text-success")
                }
                messageElement.addClass("text-danger");
                jobApplyForm.find(".form-message").html("Kindly upload your resume");

                if ($(".job-apply-submit + .loading").length) {
                    $(".job-apply-submit + .loading").remove()
                }
            }, 2500);
            
        }
        return false;
		


        // let formData    = new FormData(e.target)
        // let query       = Object.fromEntries(formData.entries())
        // let data        = {}

        // Object.keys(query).forEach(key => {
        //     data[key] = query[key]

        //     formData.delete(key)
        // })

        // formData.append("data", JSON.stringify(data));
        // formData.append("action", "jobadder_api_apply_for_job");
  
        // $.ajax({
        // 	type: "post",
        //     dataType: "json",
        //     url: wp_obj.ajax_url,
        //     data: formData,
        //     processData: false,
        //     contentType: false,
        //     enctype: 'multipart/form-data',
        //     cache: false,
        //     success: (res) => {
        //         var messageElement = jobApplyForm.find(".form-message")

        //         if (res.success) {
        //             messageElement.addClass("text-success")
        //             messageElement.html("Application submitted!")
        //         } else {
        //             messageElement.addClass("text-danger")
        //             jobApplyForm.find(".form-message").html(res.data.message)
        //         }

        //         if ($(".job-apply-submit + .loading").length) {
        //             $(".job-apply-submit + .loading").remove()
        //         }
        //     },
        //     error: (err) => {
        //     	console.log(err)
        //     }
        // })
    })
	
	$('.multipleSelect').fastselect();
})
    
	
	// function removeOptionByIndex(index) {
      //      if (index >= 0 && index < $('.multipleSelect option').length) {
        //        $('.multipleSelect option').eq(index).remove();
          //  }
        //}

        // Automatically remove the option at index 0 when the page loads
       // $(document).ready(function() {
			
         //   removeOptionByIndex(0);
        //});

		
		 function removeOptionFromAllSelects(index) {
            // Get all select elements with the class 'multiSelect'
            var selects = document.querySelectorAll('.multipleSelect');
            
            // Iterate over each select element
            selects.forEach(function(select) {
                // Remove the option at the specified index if it exists
                var options = select.options;
				let removeValue = [];
                if (index >= 0 && index < options.length) {
				 removeValue += options[index].text;
                    select.remove(index);
					
                }
			//	alert(removeValue);
            });
			
        }

        // Automatically remove the option at index 0 when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            removeOptionFromAllSelects(0);
        });
		
// function jobCoverTypeCheck(that, jobId) {
//     if (that.value == "cover-letter") {
//         document.getElementById("cover-letter-"+jobId).parentNode.style.display = "block";
//     } else {
//         document.getElementById("cover-letter-"+jobId).parentNode.style.display = "none";
//     }

//     if (that.value == "cover-note") {
//         document.getElementById("cover-note-"+jobId).parentNode.style.display = "block";
//     } else {
//         document.getElementById("cover-note-"+jobId).parentNode.style.display = "none";
//     }
// }





 