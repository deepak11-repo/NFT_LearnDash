jQuery(document).ready(function($) {
    $('#nftVerificationForm').submit(function(event) {
        event.preventDefault();
        var nftCode = $('#nftCode').val();
        
        if (!nftCode.trim()) {
            Toastify({
                text: "Please enter an NFT code.",
                duration: 2000, 
                gravity: "top", 
                position: "right", 
                backgroundColor: "linear-gradient(to right, #ff0000, #ffcccc)", 
                stopOnFocus: true 
            }).showToast();
            return;
        }

        if (/[^a-zA-Z0-9]/.test(nftCode)) {
            Toastify({
                text: "Please enter only alphanumeric characters.",
                duration: 2000, 
                gravity: "top", 
                position: "right", 
                backgroundColor: "linear-gradient(to right, #ff0000, #ffcccc)", 
                stopOnFocus: true 
            }).showToast();
            $('#nftCode').val('');
            return;
        }
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_nft_data',
                nftCode: nftCode,
                nonce: ajax_object.nonce
            },
            success: function(response) {
                var data = JSON.parse(response.data);
                console.log(data);
                $('#verificationResponse').empty();
                if (data.error === "Error fetching certificate") {
                    $('#verificationResponse').append($('<div>').addClass('error-message').text('Invalid NFT Code'));
                } else {
                    var container = $('<div>').addClass('response-container');
                    var authenticityMessage = $('<div>').addClass('confirmation-message').text('Authenticity confirmed ✅');
                    container.append(authenticityMessage);
                    var anotherMessage = $('<div>').addClass('confirmation-message').text('Below are the details associated with your authenticated NFT');
                    container.append(anotherMessage);
                    $.each(data, function(key, value) {
                        var formattedKey = key.replace(/([A-Z])/g, ' $1').replace(/^./, function(str){ return str.toUpperCase(); });
                        var label = $('<span>').addClass('response-label').text(formattedKey + ': ');
                        var span = $('<span>').addClass('response-value').text(value);
                        var item = $('<div>').addClass('response-item').append(label).append(span);
                        container.append(item).append('<br>');
                    });
                    $('#verificationResponse').append(container);
                    $('#nftCode').val('');
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                $('#verificationResponse').append($('<div>').addClass('error-message').text('Invalid NFT Code'));                
            }            
        });
    });
});