    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">About <?= APP_NAME ?></h3>
                    <p class="text-gray-400">
                        A comprehensive platform for coordinating rescue agencies during natural and man-made disasters.
                        Our mission is to enhance response time and save lives through better coordination.
                    </p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="index.php" class="hover:text-white"><i class="fas fa-chevron-right mr-2 text-xs"></i>Home</a></li>
                        <li><a href="disasters.php" class="hover:text-white"><i class="fas fa-chevron-right mr-2 text-xs"></i>Disasters</a></li>
                        <li><a href="agencies.php" class="hover:text-white"><i class="fas fa-chevron-right mr-2 text-xs"></i>Agencies</a></li>
                        <li><a href="resources.php" class="hover:text-white"><i class="fas fa-chevron-right mr-2 text-xs"></i>Resources</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Resources</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white"><i class="fas fa-chevron-right mr-2 text-xs"></i>Documentation</a></li>
                        <li><a href="#" class="hover:text-white"><i class="fas fa-chevron-right mr-2 text-xs"></i>API</a></li>
                        <li><a href="#" class="hover:text-white"><i class="fas fa-chevron-right mr-2 text-xs"></i>Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white"><i class="fas fa-chevron-right mr-2 text-xs"></i>Terms of Service</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-envelope mr-2"></i> info@rescuecoord.org</li>
                        <li><i class="fas fa-phone mr-2"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> 123 Rescue St, Safety City</li>
                    </ul>
                    <div class="mt-4 flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-6 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400">&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
                <div class="mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-white mx-2">Privacy Policy</a>
                    <a href="#" class="text-gray-400 hover:text-white mx-2">Terms of Service</a>
                    <a href="#" class="text-gray-400 hover:text-white mx-2">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Map Script -->
    <script src="assets/js/map.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
        
        // Flash message close button
        document.querySelectorAll('.flash-close').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.flash-message').remove();
            });
        });
        
        // Auto-close flash messages after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.flash-message').forEach(message => {
                message.remove();
            });
        }, 5000);
    </script>
</body>
</html>
