$(document).ready(function() {

	let mat_selected = $('[name="materiel_id"]').find('option:selected').val();
	if (mat_selected != -1) {
		$.ajax({
			url: '/custom/entretien/ajax/entretien.php?materiel_id=' + mat_selected + '&action=checkifexploited',
			beforeSend: function() { // Before we send the request, remove the .hidden class from the spinner and default to inline-block.
				$('[name="materiel_id"]').next('.select2-container').nextAll('span').remove();
				$('[name="materiel_id"]').next('.select2-container').after('<span id="loader" class="lds-dual-ring"></span>');
			},
			success: function(output) {
				$('[name="materiel_id"]').next('.select2-container').nextAll('span').remove();
				if (output) {
					$('[name="materiel_id"]').next('.select2-container').after('<span class="badge badge-status4 badge-status">En exploitation</span>');
					$.ajax({
						url: '/custom/entretien/ajax/entretien.php?materiel_id=' + mat_selected + '&action=getreplacementoption',
						success: function(outputt) {
							$('[name="replacement_materiel_id"]').html(outputt);
							$('[name="replacement_materiel_id"]').removeAttr('disabled');
						}
					});
				} else {
					$('[name="materiel_id"]').next('.select2-container').after('<span class="badge badge-status5 badge-status">Hors exploitation</span>');
					$('[name="replacement_materiel_id"]').html('<option value="-1">--Sélectionnez un matériel--</option>');
					$('[name="replacement_materiel_id"]').attr('disabled', 'disabled');
				}
			}
		});
	}

	$(document).on('change', '[name="materiel_id"]', function() {
		var selected = $(this).find('option:selected').val();
		if (selected != -1) {
			$.ajax({
				url: '/custom/entretien/ajax/entretien.php?materiel_id=' + selected + '&action=checkifexploited',
				context: this,
				beforeSend: function() { // Before we send the request, remove the .hidden class from the spinner and default to inline-block.
					$(this).next('.select2-container').nextAll('span').remove();
					$(this).next('.select2-container').after('<span id="loader" class="lds-dual-ring"></span>');
				},
				success: function(output) {
					$(this).next('.select2-container').nextAll('span').remove();
					if (output) {
						$(this).next('.select2-container').after('<span class="badge badge-status4 badge-status">En exploitation</span>');
						$.ajax({
							url: '/custom/entretien/ajax/entretien.php?materiel_id=' + selected + '&action=getreplacementoption',
							context: this,
							success: function(outputt) {
								$('[name="replacement_materiel_id"]').html(outputt);
								$('[name="replacement_materiel_id"]').removeAttr('disabled');
							}
						});
					} else {
						$(this).next('.select2-container').after('<span class="badge badge-status5 badge-status">Hors exploitation</span>');
						$('[name="replacement_materiel_id"]').html('<option value="-1">--Sélectionnez un matériel--</option>');
						$('[name="replacement_materiel_id"]').attr('disabled', 'disabled');
					}

				}
			});
		} else {
			$(this).next('.select2-container').nextAll('span').remove();
		}
	});



});
