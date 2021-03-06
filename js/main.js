

(
		function($){
			$( document ).ready(function() {
				renderHome($('.lhscolumn'));
				if ($('#DesignCanvas').length){
					$result=$('.wrapDesignCanvas').data();
					renderDesignResult($,$result['address'],$result['source'],$result['scene'],'DesignCanvas');
				}
				/**
				if ($('.wrapCanvas').length){
					$('.wrapCanvas').each(function(i, obj) {
						prepareChart($(this));
					});
				}
				**/
				
			});
		}
)(jQuery);



function renderHome($target){
	$url='backend/index.php';
	$settings=$($target).data();
	$data = {'key' : $settings['key']};
	//format indicates if we expect html or json data
	$format=$settings['format'];
	
	
	$.get($url,$data,function($result){
		console.log($result);
		if ($format=='html'){
			$target.html($result);
			if ($('#requestForm').length){
				$('#requestForm').submit(function(event){
					event.preventDefault();
					processRequestForm();
				});
			}
		}
		
	}, $format);
}


function processRequestForm(){
	
	// check that form has been completed, localized warnings are in target element data-warning attribute
	var bReturn = true;

	var warrnings = [];

		// email address
		if( !$('#emailAddress').val() ) {
			warrnings.push( showWarning($('#emailAddress')) );
			// return false;
		}
		if (!isEmail($('#emailAddress').val())){
			warrnings.push( showWarning($('#emailAddress')) );
			// return false;
		}
		
		// business unit
		if( !$('#businessUnitID').val() ) {
			warrnings.push( showWarning($('#businessUnitID')) );
			// return false;
		}
		// at least one selection 8oz / 12oz
		if (( !$('#cpc8dwQuantity').val()  ) && ( !$('#cpc12dwQuantity').val())){
			warrnings.push( showWarning($('#cpc8dwQuantity')) );
			// return false;
		}
		if (( $('#cpc8dwQuantity').val()=='0' ) && ($('#cpc12dwQuantity').val()=='0')){
			warrnings.push( showWarning($('#cpc8dwQuantity')) );
			// return false;
		}
		
		if( warrnings.length ) {
			$('#formWarnings').html( '<ul><li>' + warrnings.join('</li><li>') + '</li></ul>' );
			
			return false;
		}
		else {
			$('#formWarnings').html( '' );
		}

	// all ok so post form
		$url='backend/index.php';
		console.log($('#requestForm').serialize());
		$.post( $url, $('#requestForm').serialize(), function(data) {
			console.log(data);
			if (data['errors']===0){
				renderEstimate(data);
			}
			
		}, 'json');
		
		
		
}

function renderEstimate($estimate){
	// check if the target element already exists, if it does then remove it from the DOM

	if ($('#estimate').length){
		$('#estimate').remove();
	}
	
	$url='backend/index.php';
	$data = {
				'key' : $estimate['key'],
				'reference' : $estimate['quoteReference']
	};
	$.get($url,$data,function($result){
		console.log($result);
		
		$('.estimateResult').html($result);
		
		$([document.documentElement, document.body]).animate({
			scrollTop: $(".estimateResult").offset().top
		}, 2000);

		// bind to the button click 'send request'
		$('#sendRequest').click(function(event){
			event.preventDefault();
			saveEstimate();
		});
		
	}, 'html');
}

function saveEstimate(){
	$settings=$('#sendRequest').data();
	console.log($settings);
	$url='backend/index.php';
	$data = {
				'key' : $settings['key'],
				'reference' : $settings['reference']
	};
	$.get($url,$data,function($result){
		console.log($result);
		$('.lhscolumn').html($result);
		
		$([document.documentElement, document.body]).animate({
			scrollTop: $("body").offset().top
		}, 2000);

	}, 'html');
}



function showWarning($cause){
	$message=$cause.data('warning');
	
	return $message;
}

function isEmail(email) {
	  var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	  return regex.test(email);
	}
