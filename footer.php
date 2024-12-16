            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="copyright">
                <p>Henco</a> 2024</p>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div></div>

    <!-- Scripts -->
    <script src="plugins/common/common.min.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/settings.js"></script>
    <script src="js/gleek.js"></script>

	<script src="js/changecurrency.js"></script>
        <script>
            function populateEditModal(client) {
                document.getElementById('edit_client_id').value = client.id;
                document.getElementById('edit_name').value = client.name;
                document.getElementById('edit_email').value = client.email;
                document.getElementById('edit_phone').value = client.phone;
                document.getElementById('edit_address').value = client.address;
                document.getElementById('edit_city').value = client.city;
                document.getElementById('edit_state').value = client.state;
                document.getElementById('edit_zip').value = client.zip;
            }
        </script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="js/translator.js"></script>
<script src="js/pages.js"></script>   
</body>

</html>