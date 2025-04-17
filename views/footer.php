</div>
</div>

<!-- Footer -->
<div class="footer">
    <div class="copyright">
        <p>Henco</a> 2024</p> <!-- Copyright information -->
    </div>
</div>
</div>
</div>
</div>

<!-- Scripts -->

<!-- Common plugins -->
<script src="plugins/common/common.min.js"></script> 

<!-- Theme script -->
<script src="js/custom.min.js"></script> 
<script src="js/settings.js"></script>  
<script src="js/gleek.js"></script>     
<script src="js/styleSwitcher.js"></script> 

<!-- Currency change script -->
<script src="js/changecurrency.js"></script> 

<!-- Client edit modal population script -->
<script>
    /**
     * Populates the edit client modal with client data.
     *
     * @param {object} client The client data to populate the modal with.
     */
    function populateEditModal(client) {
        document.getElementById('edit_client_id').value = client.id;       // Set client ID
        document.getElementById('edit_name').value = client.name;           // Set client name
        document.getElementById('edit_email').value = client.email;         // Set client email
        document.getElementById('edit_phone').value = client.phone;         // Set client phone
        document.getElementById('edit_address').value = client.address;     // Set client address
        document.getElementById('edit_city').value = client.city;           // Set client city
        document.getElementById('edit_state').value = client.state;         // Set client state
        document.getElementById('edit_zip').value = client.zip;             // Set client ZIP code
    }
</script>

<!-- jQuery library -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 

<!-- DataTables library -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> 

<!-- Translation script -->
<script src="js/translator.js"></script> 

<!-- Page-specific scripts -->
<script src="js/pages.js"></script>    
</body>

</html>