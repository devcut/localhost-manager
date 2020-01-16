import 'select2';

let $inputConfigurationFolder = $('.js-configuration-folder');
let $inputConfigurationException = $('.js-configuration-exception');

$inputConfigurationException.select2({
    width: '100%'
});

$inputConfigurationFolder.on('change', function () {

    $.ajax({
        url: 'configuration/development-folder',
        type: 'POST',
        data: {folder: $inputConfigurationFolder.val()},
        success: function (data) {
            $.each(data, function(i) {
                // Add new option only if not already exists in select
                if($inputConfigurationException.find('option[value="' + data[i].text + '"]').length === 0) {
                    let newOption = new Option(data[i].text, data[i].id, false, false);
                    $inputConfigurationException.append(newOption).trigger('change');
                }
            });
        },
        // Remove all option if path folder is not write
        error: function () {
            $inputConfigurationException.find('option').remove();
        }
    });

}).trigger('change');