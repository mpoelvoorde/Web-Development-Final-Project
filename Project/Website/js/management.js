$(document).ready(function() {
	
	// Populate room type descriptions
	var roomType = $('#room-type-select').val();
	
	$.get('roomtypeselect.php?roomtype=' + roomType, function(data, status) {
		$('#room-type-description').val(data);
	});
	
	// Fill by default with Canadian provinces:
	$.get('stategenerator.php?country=CAN', function(data, status) {
		$('#state').html(data);
	});
	
	
	// Functions
	$('#room-type-select').change(function() {
		var roomType = $('#room-type-select').val();
	
		$.get('roomtypeselect.php?roomtype=' + roomType, function(data, status) {
			$('#room-type-description').val(data);
		});
	});	
	
	// When country changes:
	$('#country').change(function() {
		var selectedCountry = $('#country').val();
		
		// If country selected is Canada or U.S., populate states
		if (selectedCountry == 'CAN') {
			$('#state-label').text('Province:');
			$('#state').prop('disabled', false);
			
			// USE AJAX TO POPULATE WITH CANADIAN PROVINCES
			$.get('stategenerator.php?country=CAN', function(data, status) {
				$('#state').html(data);
			});
		}
		
		else if (selectedCountry == 'USA') {
			$('#state-label').text('State:');
			$('#state').prop('disabled', false);
			
			// USE AJAX TO POPULATE WITH U.S. STATES
			$.get('stategenerator.php?country=USA', function(data, status) {
				$('#state').html(data);
			});
		}
		
		// Else, make sure 'state' is grayed out
		else {
			$('#state').prop('disabled', true);
		}
	});
	
});