/**
 * APSS Search Overlay Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    const triggers = document.querySelectorAll('.apss-trigger');
    const overlay = document.getElementById('apss-search-overlay');
    const closeBtn = document.querySelector('.apss-close');
    const searchInput = document.getElementById('apss-search-input');
    const resultsContainer = document.getElementById('apss-search-results');
    const spinner = document.querySelector('.apss-spinner');
    
    let abortController = null;
    let debounceTimer = null;

    /**
     * Simple HTML escaping helper to prevent XSS.
     */
    const escapeHTML = (str) => {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    };

    if (!triggers.length || !overlay || !closeBtn || !searchInput) return;

    // Toggle Overlay
    const openOverlay = () => {
        overlay.style.display = 'flex';
        document.body.classList.add('apss-overlay-open');
        setTimeout(() => {
            searchInput.focus();
        }, 100);
    };

    const closeOverlay = () => {
        overlay.style.display = 'none';
        document.body.classList.remove('apss-overlay-open');
        searchInput.value = '';
        resultsContainer.innerHTML = '';
        if (abortController) abortController.abort();
    };

    triggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            openOverlay();
        });
    });

    closeBtn.addEventListener('click', closeOverlay);

    // Close on ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.style.display === 'flex') {
            closeOverlay();
        }
    });

    // Close on background click (optional but nice)
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeOverlay();
        }
    });

    // Search Logic
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.trim();

        clearTimeout(debounceTimer);
        if (abortController) abortController.abort();

        if (term.length < 2) {
            resultsContainer.innerHTML = '';
            spinner.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            performSearch(term);
        }, 300);
    });

    async function performSearch(term) {
        spinner.style.display = 'block';
        resultsContainer.style.opacity = '0.5';

        abortController = new AbortController();

        try {
            const response = await fetch(`${apssSearchData.root}apss/v1/search?term=${encodeURIComponent(term)}`, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': apssSearchData.nonce
                },
                signal: abortController.signal
            });

            if (!response.ok) throw new Error('Search failed');

            const results = await response.json();
            renderResults(results);
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('Search aborted');
            } else {
                console.error('Search error:', error);
                resultsContainer.innerHTML = '<p class="apss-no-results">Something went wrong. Please try again.</p>';
            }
        } finally {
            spinner.style.display = 'none';
            resultsContainer.style.opacity = '1';
        }
    }

    function renderResults(groupedResults) {
        if (!groupedResults || Object.keys(groupedResults).length === 0) {
            resultsContainer.innerHTML = '<p class="apss-no-results">No results found.</p>';
            return;
        }

        let html = '';

        for (const [postType, results] of Object.entries(groupedResults)) {
            const rawLabel = apssSearchData.labels[postType] || postType.charAt(0).toUpperCase() + postType.slice(1);
            const label = escapeHTML(rawLabel);
            
            html += `
                <div class="apss-results-section">
                    <div class="apss-section-header">
                        <h2 class="apss-section-title">${label}</h2>
                        <hr class="apss-section-separator">
                    </div>
                    <div class="apss-results-grid">
                        ${results.map(item => `
                            <a href="${item.permalink}" class="apss-result-item">
                                <div class="apss-result-image">
                                    ${item.image ? `<img src="${item.image}" alt="${escapeHTML(item.title)}">` : ''}
                                </div>
                                <div class="apss-result-content">
                                    <h3 class="apss-result-title">${escapeHTML(item.title)}</h3>
                                    <p class="apss-result-excerpt">${escapeHTML(item.excerpt)}</p>
                                    <div class="apss-result-meta">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        <span>${escapeHTML(item.date)}</span>
                                    </div>
                                </div>
                            </a>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        resultsContainer.innerHTML = html;
    }
});
