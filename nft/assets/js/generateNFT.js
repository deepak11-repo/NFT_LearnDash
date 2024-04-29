jQuery(document).ready(function($) {
    console.log("Script loaded successfully");
    $(document).on('click', 'button.verify-button', function() {
        var clickedButton = $(this); 
        console.log("Button clicked");

        // Get the data from the corresponding table row
        var rowData = $(this).closest('tr').find('td');
        var courseName = rowData.eq(1).text();
        var studentName = rowData.eq(2).text();
        var studentMail = rowData.eq(3).text();
        var enrollmentDate = rowData.eq(4).text();
        var completionDate = rowData.eq(5).text();
        console.log("Extracted data:", courseName, studentName, enrollmentDate, completionDate);

        // Prepare data object
        var data = {
            'studentName': studentName,
            'courseName': courseName,
            'enrollmentDate': enrollmentDate,
            'completionDate': completionDate
        };
        console.log("Data object:", data);

        // Perform AJAX request to issue certificate
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'issue_certificate_ajax',
                data: data,
                studentMail: studentMail 
            },
            success: function(response) {
                console.log("Success", response);
                Toastify({
                    text: "Successfully generated NFT",
                    duration: 2000, 
                    gravity: "top", 
                    position: "right", 
                    backgroundColor: "linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(9,121,16,1) 26%, rgba(0,212,255,1) 86%)",
                    stopOnFocus: true 
                }).showToast();
                
                clickedButton.prop('disabled', true);
                var id = clickedButton.data('id');                
                $.ajax({
                    url: ajax_object_update_nft.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'update_nft_generated',
                        id: id
                    },
                    success: function(response) {
                        console.log("nftGenerated updated successfully");
                    },
                    error: function(xhr, status, error) {
                        console.error("Error updating nftGenerated:", error);
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
            }
        });
        
    });
});
