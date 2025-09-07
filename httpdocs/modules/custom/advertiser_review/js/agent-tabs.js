(function (Drupal) {
  'use strict';

  Drupal.behaviors.agentTabs = {
    attach: function (context, settings) {
      // Find all tab links that haven't been processed yet
      const tabLinks = context.querySelectorAll('.agent-tab-link:not([data-agent-tabs-processed])');

      tabLinks.forEach(function(tabLink) {
        // Mark as processed
        tabLink.setAttribute('data-agent-tabs-processed', 'true');

        tabLink.addEventListener('click', function(e) {
          e.preventDefault();

          const tab = this.getAttribute('data-tab');
          const nodeId = this.getAttribute('data-node-id');

          loadTabContent(tab, nodeId);
          updateTabStates(tab, this);
        });
      });

      // Handle pagination links within tab content - use Bootstrap pagination classes
      const paginationLinks = context.querySelectorAll('.agent-tab-pane .page-link:not([data-pagination-processed])');
      console.log('Found pagination links:', paginationLinks.length);

      paginationLinks.forEach(function(paginationLink) {
        // Skip if this is just a span (current page indicator)
        if (paginationLink.tagName.toLowerCase() === 'span') {
          return;
        }

        // Mark as processed
        paginationLink.setAttribute('data-pagination-processed', 'true');

        paginationLink.addEventListener('click', function(e) {
          e.preventDefault();

          console.log('Pagination link clicked:', this.href);

          // Get the current active tab
          const activeTab = document.querySelector('.agent-tab-link.active');
          if (!activeTab) {
            console.log('No active tab found');
            return;
          }

          const tab = activeTab.getAttribute('data-tab');
          const nodeId = activeTab.getAttribute('data-node-id');
          const url = new URL(this.href, window.location.origin);
          const page = parseInt(url.searchParams.get('page')) || 0;

          console.log('Tab:', tab, 'NodeId:', nodeId, 'Page:', page);

          loadTabContent(tab, nodeId, page);
        });
      });

      // Load first tab content on page load if not already loaded
      const firstActiveTab = context.querySelector('.agent-tab-link.active');
      if (firstActiveTab) {
        const firstTabId = firstActiveTab.getAttribute('data-tab');
        const firstTabContent = document.getElementById('tab-content-' + firstTabId);

        if (firstTabContent && firstTabContent.children.length === 0) {
          const nodeId = firstActiveTab.getAttribute('data-node-id');
          loadTabContent(firstTabId, nodeId);
        }
      }
    }
  };

  /**
   * Load tab content via AJAX
   */
  function loadTabContent(tab, nodeId, page = 0) {
    const contentElement = document.getElementById('tab-content-' + tab);

    if (!contentElement) {
      console.error('Tab content element not found:', 'tab-content-' + tab);
      return;
    }

    // Only skip reload for initial tab switching (when no page is explicitly requested)
    const activeTabLink = document.querySelector('.agent-tab-link.active');
    if (arguments.length === 2 && activeTabLink &&
      activeTabLink.getAttribute('data-tab') === tab &&
      activeTabLink.classList.contains('active') &&
      contentElement.children.length > 0) {
      return;
    }

    // Show loading spinner
    contentElement.innerHTML = `
      <div class="text-center p-4">
        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
        <div class="mt-2">Loading...</div>
      </div>
    `;

    // Build URL with page parameter if needed
    let url = '/agent/' + nodeId + '/tab/' + tab;
    if (page && page > 0) {
      url += '?page=' + page;
    }

    console.log('Making AJAX request to:', url);

    // Make AJAX request using fetch
    fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(function(response) {
        if (!response.ok) {
          throw new Error('HTTP ' + response.status + ': ' + getErrorMessage(response.status));
        }
        return response.json();
      })
      .then(function(data) {
        if (data.content) {
          contentElement.innerHTML = data.content;
          // Trigger Drupal behaviors on new content (this will handle new pagination links)
          Drupal.attachBehaviors(contentElement);
        } else {
          contentElement.innerHTML = '<div class="alert alert-warning">No content available</div>';
        }
      })
      .catch(function(error) {
        console.error('Error loading tab content:', error);
        contentElement.innerHTML = `
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle"></i> ${error.message}
        </div>
      `;
      });
  }

  /**
   * Update tab active states
   */
  function updateTabStates(activeTab, activeLink) {
    // Remove active class from all tab links
    const allTabLinks = document.querySelectorAll('.agent-tab-link');
    allTabLinks.forEach(function(link) {
      link.classList.remove('active');
    });

    // Remove active classes from all tab panes
    const allTabPanes = document.querySelectorAll('.agent-tab-pane');
    allTabPanes.forEach(function(pane) {
      pane.classList.remove('show', 'active');
    });

    // Add active class to clicked link
    activeLink.classList.add('active');

    // Add active classes to corresponding tab pane
    const activePane = document.getElementById('tab-content-' + activeTab);
    if (activePane) {
      activePane.classList.add('show', 'active');
    }
  }

  /**
   * Get user-friendly error message
   */
  function getErrorMessage(status) {
    switch (status) {
      case 403:
        return 'Access denied';
      case 404:
        return 'Content not found';
      case 500:
        return 'Server error';
      default:
        return 'Error loading content';
    }
  }

})(Drupal);
