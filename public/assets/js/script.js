$("#registration_form_beOrganisateur" ).click(function() {
	if($('#bloc_address').hasClass('d-none'))
	{
		$('#bloc_address').removeClass('d-none');
		$('#bloc_address').show("slow");
	}
	else
	{
		$('#bloc_address').hide("slow", function(){
			$('#bloc_address').addClass('d-none');
		});
	}
});

$(document).ready(function() {
	let items = document.querySelectorAll('#recipeCarousel .carousel-item')
	$('#recipeCarousel .carousel-item').first().addClass('active');
	items.forEach((el) => {
		const minPerSlide = 4
		let next = el.nextElementSibling
		for (var i=1; i<minPerSlide; i++) {
			if (!next) {
				// wrap carousel by using first child
				next = items[0]
			}
			let cloneChild = next.cloneNode(true)
			el.appendChild(cloneChild.children[0])
			next = next.nextElementSibling
		}
	})
});
