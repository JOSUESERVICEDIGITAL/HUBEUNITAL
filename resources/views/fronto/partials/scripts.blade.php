
 <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('fronto/lib/easing/easing.min.js')}}"></script>
<script src="{{ asset('fronto/lib/owlcarousel/owl.carousel.min.js')}}"></script>
<!-- <script type="text/javascript" src="https://platform-api.sharethis.com/js/sharethis.js#property=686882296689fb0019a07753&product=sop" async="async"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js" integrity="sha512-Zq9o+E00xhhR/7vJ49mxFNJ0KQw1E1TMWkPTxrWcnpfEFDEXgUiwJHIKit93EW/XxE31HSI5GEOW06G6BF1AtA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Contact Javascript File -->
    <script src="{{ asset('fronto/mail/jqBootstrapValidation.min.js')}}"></script>
    <script src="{{ asset('fronto/mail/contact.js')}}"></script>

    <!-- Template Javascript -->
    <script src="{{ asset('fronto/js/main.js')}}"></script>


<script>
    document.querySelector('form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Envoi en cours...';
    
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Afficher un message de succès
            alert(data.message);
            closePopup();
        } else {
            // Afficher les erreurs de validation
            if (data.errors) {
                let errorMessages = [];
                for (const [field, messages] of Object.entries(data.errors)) {
                    errorMessages.push(messages.join('\n'));
                }
                alert("Erreurs :\n" + errorMessages.join('\n\n'));
            } else {
                alert(data.message || 'Une erreur est survenue');
            }
        }
    } catch (error) {
        alert('Erreur réseau : ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    }
});
</script>








   