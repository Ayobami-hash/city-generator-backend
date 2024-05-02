jQuery(document).ready(function ($) {
    window.onStateChange = function () {
        const state = $('#stateSelect').val();
        console.log('Selected state:', state); // Log selected state

        $('#stateSelect').hide();
        $('#countyContainer').show();

        console.log($('#city_generator_nonce').val());
        console.log('AJAX URL:', city_generator.ajax_url);

        // Trigger AJAX request to load counties
        $.ajax({
            url:  city_generator.ajax_url,
            type: 'POST',
            data: {
                action: 'load_counties',
                state: state, // Ensure the state is set
                ajax_nonce: $('#city_generator_nonce').val(), // nonce
            },
            success: function (response) {
                console.log('County response:', response); // Log AJAX response

                $('#countySelect').empty().append('<option value="" disabled selected>Select a County</option>');
                response.data.counties.forEach(function (county) {
                    $('#countySelect').append(`<option value="${county}">${county}</option>`);
                });
            },
            error: function (error) {
                console.error('Error loading counties:', error); // Log AJAX error
            },
        });
    };
    

    // Add similar logging and AJAX calls for onCountyChange function
    window.onCountyChange = function () {
        const county = $('#countySelect').val();
        console.log('Selected county:', county); // Log selected state

        $('#countySelect').hide();
        $('#citySelect').show();

        // Trigger AJAX request to load counties
        $.ajax({
            url:  city_generator.ajax_url,
            type: 'POST',
            data: {
                action: 'load_cities',
                county: county,

            },
            success: function (response) {
                console.log('City response:', response); // Log AJAX response

                response.data.cities.forEach(function (city) {
                    $('#citySelect').append(`<label style="margin-right:10px"> ${city} <input type='checkbox' name="city" value=${city} /></label><br>  `)
                })
                $('#citySelect').append(`<button onclick="continueToPrompt()">Continue</button>`)
            },
            error: function (error) {
                console.error('Error loading counties:', error); // Log AJAX error
            },
        });
    };

    window.continueToPrompt = function () {
        $('#citySelect').hide();
        $('#shortcodePromptSection').show();
        $('#publishSection').show();

    }
    // Additional functions for shortcode and content generation

    
    window.addMoreShortcodes = function () {
    const shortcodeInput = '<input type="text" name="shortcode[]" placeholder="{{shortcode}}" />';
    const promptInput = '<input type="text" name="prompt[]" placeholder="Enter your prompt" />';
    // const addMoreButton = '<button onclick="addMoreShortcodes()">Add More</button>'
    // const generateButton = '<button onclick="generateContent()">Generate Content</button>'
    $('#shortcodePromptSection').append(shortcodeInput).append(promptInput).append('<br>');
}


window.generateContent = async function () {
    const cities = $('input[name="city"]:checked').map(function () {
        return $(this).val();
    }).get();

    const shortcodes = $('input[name="shortcode[]"]').map(function () {
        return $(this).val();
    }).get();

    const prompts = $('input[name="prompt[]"]').map(function () {
        return $(this).val();
    }).get();

    for (const city of cities) {
        const aContent = {};
        const moreContent = {};
        const mappedValues = {};

        for (const prompt of prompts) {
            // Construct the prompt with the city information
            const combinedPrompt = `${prompt} Write about the city of ${city}.`;
            try {
                // const apikey = 'YOUR_OPENAI_API_KEY';
                const apikey = 'sk-proj-E5Jai0c1CaQ1RuznP8MTT3BlbkFJWbVTAyMiYuWx8cAi1BjU';
                const apiEndpoint = 'https://api.openai.com/v1/chat/completions';
                const requestData = {
                    model: 'gpt-3.5-turbo',
                    messages: [{ role: 'user', content: combinedPrompt }],
                    temperature: 0.7,
                };

                const response = await $.ajax({
                    url: apiEndpoint,
                    type: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${apikey}`,
                    },
                    data: JSON.stringify(requestData),
                });

                const chatResponse = response.choices[0].message.content;

                aContent[prompt] = chatResponse;
                
            } catch (error) {
                console.error('Error generating content:', error);
            }
    }
         console.log(aContent);


        $.each(moreContent, function(key, value) {
            const correspondingValue = aContent[key];
            mappedValues[key] = correspondingValue;
        });

        // Send cityContent to server to create city page
        $.ajax({
            url: my_custom_plugin.ajax_url,
            type: 'POST',
            data: {
                action: 'create_city_page',
                city: city,
                shortcodes: shortcodes,
                prompts: prompts,
                content: aContent,
            },
            success: function (response) {
                console.log('City page created successfully:', response);
                console.log(city);
                console.log(shortcodes);
                console.log(prompts);
                // console.log(cityContent);
                console.log(mappedValues);
            },
            error: function (error) {
                console.error('Error creating city page:', error);
            },
        });
    }
    alert('Content Published!');
};

});
