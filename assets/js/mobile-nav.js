// Mobile Navigation Handler - works across all pages
// This script handles mobile navigation active states and hash link scrolling

document.addEventListener('DOMContentLoaded', () => {
    // Mobile navigation active state based on hash or current page
    const updateMobileNavActive = () => {
        const mobileNavItems = document.querySelectorAll('.mobile-nav-item');
        const currentHash = window.location.hash;
        const currentPath = window.location.pathname;
        const currentPage = currentPath.split('/').pop() || 'index.php';
        
        mobileNavItems.forEach(item => {
            item.classList.remove('active');
            const href = item.getAttribute('href');
            
            if (!href) return;
            
            // Check if it's a hash link (could be #quotes or index.php#quotes)
            if (href.includes('#')) {
                const hashPart = href.split('#')[1];
                const currentHashPart = currentHash.substring(1);
                
                // If we're on the same page and hash matches
                if (hashPart === currentHashPart) {
                    item.classList.add('active');
                }
                // Default to quotes on index page with no hash
                else if ((currentPage === 'index.php' || currentPage === '') && !currentHash && hashPart === 'quotes') {
                    item.classList.add('active');
                }
            }
            // Check if the href matches the current page
            else {
                const linkPage = href.split('/').pop().split('?')[0];
                if (linkPage && (currentPage === linkPage || (currentPage === '' && linkPage === 'index.php'))) {
                    item.classList.add('active');
                }
            }
        });
    };

    // Update active state on hash change
    window.addEventListener('hashchange', updateMobileNavActive);
    
    // Handle hash link clicks for smooth scrolling
    document.querySelectorAll('.mobile-nav-item').forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            
            if (!href) return;
            
            // Only handle hash links on the current page
            if (href.startsWith('#')) {
                const targetId = href.substring(1);
                const targetEl = document.getElementById(targetId);
                
                if (targetEl) {
                    e.preventDefault();
                    
                    // Update URL hash
                    window.location.hash = href;
                    
                    // Smooth scroll to target
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    
                    // Update active state immediately
                    updateMobileNavActive();
                }
            }
            // Handle cross-page hash links (e.g., index.php#quotes from another page)
            else if (href.includes('#') && !href.startsWith('http')) {
                const [pagePart, hashPart] = href.split('#');
                const currentPath = window.location.pathname;
                const currentPage = currentPath.split('/').pop() || 'index.php';
                const targetPage = pagePart.split('/').pop() || 'index.php';
                
                // If we're already on the target page, just scroll
                if (currentPage === targetPage) {
                    const targetEl = document.getElementById(hashPart);
                    if (targetEl) {
                        e.preventDefault();
                        window.location.hash = '#' + hashPart;
                        targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        updateMobileNavActive();
                    }
                }
                // Otherwise, let the browser navigate normally
            }
        });
    });
    
    // Initial update
    updateMobileNavActive();
});
