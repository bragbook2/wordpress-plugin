let load_more_count = 1;

document.addEventListener('DOMContentLoaded', function () {
  fetchCaseData(load_more_count);
});

document.addEventListener('click', function (event) {
  let target = event.target.closest('.ajax-load-more');
  if (target) {
    event.preventDefault();
    let loadMoreButton = target.querySelector('.bb_ajax-load-more-btn');
    if (loadMoreButton) {
      // Get the current offset from data-offset
      let currentOffset = parseInt(loadMoreButton.getAttribute('data-offset'), 10);
      fetchCaseData(currentOffset);
      // Increment the count by 1
      currentOffset += 1;
      loadMoreButton.setAttribute('data-offset', currentOffset);
    }
  }
});


function fetchCaseData(load_more_count) {
  var elementId = '';
  const pathSegments = window.location.pathname.split('/').filter(Boolean);
  const secondPart = pathSegments[0] || "";
  const thirdPart = pathSegments[1] || "";
  const forthPart = pathSegments[2] || "";

  const count = load_more_count;
  const targetHref = `/${secondPart}/${thirdPart}/`;
  const targetElement = document.querySelector(`a[href="${targetHref}"]`);

  if (targetElement) {
    elementId = targetElement.id;
    var apiToken = targetElement.getAttribute("data-api-token");
    var websitePropertyId = targetElement.getAttribute("data-website-property-id");
    var textOnly = Array.from(targetElement.childNodes)
      .filter(node => node.nodeType === Node.TEXT_NODE) // Get only text nodes
      .map(node => node.textContent.trim()) // Trim whitespace
      .join(" ");

    // Change color and opacity
    targetElement.style.color = '#000';
    targetElement.style.opacity = '1';

    // Open the corresponding accordion panel 
    let accordionButton = targetElement.closest('.bb-panel')?.previousElementSibling;
    if (accordionButton && accordionButton.classList.contains('bb-accordion')) {
      accordionButton.classList.add('active');

      let panel = accordionButton.nextElementSibling;
      if (panel && panel.classList.contains('bb-panel')) {
        panel.style.display = 'block';
        panel.style.maxHeight = panel.scrollHeight + "px";
      }
    }
  }

  const data = {
    action: 'bb_case_api',
    count: count,
    pageSlug: secondPart,
    procedureId: elementId,
    apiToken: apiToken,
    websitePropertyId: websitePropertyId,
    caseId: forthPart,

  };

  fetch(bb_plugin_data.ajaxurl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: new URLSearchParams(data).toString()
  })
    .then(response => response.json())
    .then(data => {
      try {
        if (data.data && data.data.case_set) {
          let caseSet = JSON.parse(data.data.case_set);
          if (forthPart == "") {
            var bb_case_count = (count - 1) * 10;
            let contentBox = document.querySelector('.bb-content-boxes');
           
            caseSet.data.forEach(caseItem => {
              if (caseItem.photoSets && caseItem.photoSets.length > 0) {
                let photoSet = caseItem.photoSets[0];
                let imgSrc = photoSet.highResPostProcessedImageLocation ||
                  photoSet.postProcessedImageLocation ||
                  photoSet.originalBeforeLocation;
                let imgAlt = photoSet.seoAltText || "Procedure Image";
                let caseId = caseItem.id;
                let caseDetails = caseItem.details || '';
                caseItem.patientCount = ++bb_case_count;
                let procedureUrl = `/${secondPart}/${thirdPart}/${caseId}/`;
               
                let newContent = `
                            <div class="bb-content-box">
                                <div class="bb-content-thumbnail">
                                    <a href="${procedureUrl}">
                                        <img src="${imgSrc}" alt="${imgAlt}">
                                    </a>
                                    <img class="bb-heart-icon bb-open-fav-modal" 
                                        data-case-id="${caseId}"
                                        data-bb_api_token="${apiToken}" 
                                        data-bb_website_id="${websitePropertyId}" 
                                        src="${bb_plugin_data.heartRed}" 
                                        alt="heart">
                                </div>
                                <div class="bb-content-box-inner">
                                    <div class="bb-content-box-inner-left">
                                        <h5>${thirdPart} : Patient ${caseItem.patientCount}</h5>
                                        <p>${caseDetails}</p> 
                                    </div>
                                    <div class="bb-content-box-inner-right">
                                        <img class="bb-open-fav-modal" 
                                            data-case-id="${caseId}" 
                                            data-bb_api_token="d49236c4-6288-409e-9218-a732e5eb5a1a" 
                                            data-bb_website_id="75" 
                                            src="${bb_plugin_data.heartRed}" 
                                            alt="heart">
                                    </div>
                                </div>
                                <div class="bb-content-box-cta">
                                    <a class="view-more-btn" href="${procedureUrl}">
                                        View More
                                    </a>
                                </div>
                            </div>
                        `;

                contentBox.innerHTML += newContent;
              }
            });
          } else {

            let patienLeftBox = document.querySelector('.bb-patient-left');
            if (patienLeftBox) {
              caseSet.data.forEach(caseItem => {
                let caseId = caseItem.id;

                if (caseId == forthPart) {
                  if (caseItem.photoSets && caseItem.photoSets.length > 0) {
                    caseItem.photoSets.forEach((value) => {
                      let bb_new_image_value = value.highResPostProcessedImageLocation ??
                        value.postProcessedImageLocation ??
                        value.originalBeforeLocation;

                      let imgElement = document.createElement("img");
                      imgElement.className = "bbrag_gallery_image testing-image";
                      imgElement.src = bb_new_image_value;
                      imgElement.alt = value.seoAltText ?? "Procedure Image";
                      patienLeftBox.appendChild(imgElement);
                    });

                  }
                }

              });
            }
            let patientRightBox = document.querySelector('.bb-patient-right');
            if (patientRightBox) {
              caseSet.data.forEach(caseItem => {
                let patientDetail = caseItem.details || '';
                let height = caseItem.height ? `<li>HEIGHT: ${caseItem.height.toString().toLowerCase()}</li>` : '';
                let width = caseItem.weight ? `<li>WEIGHT: ${caseItem.weight.toString().toLowerCase()}</li>` : '';
                let race = caseItem.ethnicity ? `<li>RACE: ${caseItem.ethnicity.toLowerCase()}</li>` : '';
                let gender = caseItem.gender ? `<li>GENDER: ${caseItem.gender.toLowerCase()}</li>` : '';
                let age = caseItem.age ? `<li>AGE: ${caseItem.age.toString().toLowerCase()}</li>` : '';
                let timeframe = caseItem.after1Timeframe && caseItem.after1Unit
                  ? `<li>POST-OP PERIOD: ${caseItem.after1Timeframe.toString().toLowerCase()} ${caseItem.after1Unit.toLowerCase()}</li>`
                  : '';
                let timeframe2 = caseItem.after2Timeframe && caseItem.after2Unit
                  ? `<li>2nd AFTER: ${caseItem.after2Timeframe.toString().toLowerCase()} ${caseItem.after2Unit.toLowerCase()}</li>`
                  : '';
                let revisionSurgery = caseItem.revisionSurgery
                  ? `<li>This case is a revision of a previous procedure.</li>`
                  : '';

                let bb_right_data = `
                        <div class="bb-patient-row">
                            <h2>${textOnly}</h2>
                            <img class="bb-heart-icon bb-open-fav-modal" 
                                data-case-id="${forthPart}" 
                                data-bb_api_token="${apiToken}" 
                                data-bb_website_id="${websitePropertyId}" 
                                src="${bb_plugin_data.heartRed}" alt="heart">
                        </div>
                        <ul>
                            ${height}
                            ${width}
                            ${race}
                            ${gender}
                            ${age}
                            ${timeframe}
                            ${timeframe2}
                            ${revisionSurgery}
                        </ul>
                        <p>${patientDetail}</p>
                         <div class="bb-patient-slides">
                          <ul id="pagination-list-${caseItem.id}" class="bb-pagination"></ul>
                        </div>
                    `;

                patientRightBox.innerHTML += bb_right_data;
                let paginationData = generatePagination(caseItem.caseIds, caseItem.id);
                renderPagination(paginationData, caseItem.id, targetHref);
              
              });
            }
          } 
        } else {
          console.error('Invalid response structure:', data);
        }
        if (data.data && data.data.filter_data) {
          let filterContent = document.querySelector('.bb-filter-content-inner');
          let filter_data = JSON.parse(data.data.filter_data);
          
          
      
          // Static filters (e.g., height, weight, etc.)
          if (filter_data.data.staticFilter) {
            
              for (let filterKey in filter_data.data.staticFilter) {
                  let filterGroup = filter_data.data.staticFilter[filterKey];
                  let filterHTML = `<div class="bb-filter-content-inner-wrapper">
                                      <button class="accordion">${filterKey} <img src="${bb_plugin_data.heartdown}" alt="down"></button>
                                      <div class="panel">
                                          <div class="bb-filter-select-wrapper">
                                              <div class="bb-input-box">`;
      
                  filterGroup.forEach(option => {
                      filterHTML += `<label class="bb-checkbox-container" for="m-${filterKey}-${option.value}">
                                      ${option.label}
                                      <input type="checkbox" id="m-${filterKey}-${option.value}" data-filter="${filterKey}" data-value="${option.value}" onchange="handleDynamicCheckboxChange(this)">
                                      <span class="checkmark"></span>
                                     </label>`;
                  });
       
                  filterHTML += `</div></div></div></div>`;
                  filterContent.innerHTML += filterHTML; // Append static filter HTML
              }
          }
      
          // Dynamic filters (e.g., Facelift Technique, Filler Type, etc.)
          if (filter_data.data.dynamicFilter) {
              //if(filter_data.data.dynamicFilter.length > 0){
                var filterHTMLA = `<div class='advanced-filters'><span>Advanced Filters</span></div>`;
             // }
              for (let filterKey in filter_data.data.dynamicFilter) {
                  let filterGroup = filter_data.data.dynamicFilter[filterKey];
                   filterHTMLA += `<div class="bb-filter-content-inner-wrapper">
                                      <button class="accordion">${filterKey} <img src="${bb_plugin_data.heartdown}" alt="down"></button>
                                      <div class="panel">
                                          <div class="bb-filter-select-wrapper"><div class="bb-input-box">`;
                  
                  filterGroup.forEach(option => {
                      let formattedKey = filterKey;
                      let formattedOption = option;
                      filterHTMLA += `<label class="bb-checkbox-container" for="${formattedKey}-${formattedOption}">
                                      ${option}
                                      <input type="checkbox" id="${formattedKey}-${formattedOption}" data-filter="${formattedKey}" data-value="${formattedKey}-${formattedOption}" onchange="handleDynamicCheckboxChange(this)">
                                      <span class="checkmark"></span>
                                     </label>`;
                  });
      
                  filterHTMLA += `</div></div></div></div>`;
              }
              filterContent.innerHTML += filterHTMLA; // Append dynamic filter HTML
          }
          bb_advance_filter();
        }
      
      } catch (err) {
        console.error('Error parsing case_set JSON:', err);
      }
    })
    .catch(error => {
      console.error('Error during AJAX request:', error);
    });
}

function bb_advance_filter() {
  let filterAccInner = document.querySelectorAll(".bb-filter-content-inner-wrapper .accordion");

  for (let i = 0; i < filterAccInner.length; i++) {
    filterAccInner[i].addEventListener("click", function () {

      this.classList.toggle("active");
      var panel = this.nextElementSibling; 
      if (panel.style.maxHeight) {
        panel.style.maxHeight = null;
      } else {
        panel.style.maxHeight = panel.scrollHeight + "px"; 
      }
    });
  }
  const checkboxes = document.querySelectorAll('.bb-checkbox-container input[type="checkbox"]');
  const clearButton = document.getElementById('clearButton');
  clearButton.addEventListener('click', function() {
      checkboxes.forEach(checkbox => {
          checkbox.checked = false;
      });
      const contentBoxes = document.querySelectorAll('.bb-content-box');
      contentBoxes.forEach(box => {
          box.style.display = 'block';
      });
  });
}
// Function to generate pagination data
function generatePagination(caseIds, currentCaseId) {
  return caseIds.map((id, index) => ({
    id,
    caseNumber: index + 1, // Page numbers start from 1
    isCurrent: id === currentCaseId,
  }));
}
  
function renderPagination(paginationData, caseId, targetHref) {
  const paginationList = document.getElementById(`pagination-list-${caseId}`);
  if (!paginationList || !paginationData.length) return;

  paginationList.innerHTML = ""; 
  let baseUrl = window.location.origin + targetHref;

  // Find the current page index
  const currentPageIndex = paginationData.findIndex(item => item.id === caseId);
  const totalPages = paginationData.length;

  const hasPrevious = currentPageIndex > 0;
  const hasNext = currentPageIndex < totalPages - 1;

  // Previous button
  if (hasPrevious) {
    const prevPageId = paginationData[currentPageIndex - 1].id;
    const prevItem = document.createElement("li");
    prevItem.innerHTML = `<a href="${baseUrl}${prevPageId}">Previous</a>`;
    paginationList.appendChild(prevItem);
  }

  // Define the pagination window (4 pages)
  let start = Math.max(0, currentPageIndex - 2);
  let end = Math.min(totalPages, start + 4);

  // Adjust window if near start or end
  if (end - start < 4) {
    start = Math.max(0, end - 4);
  }

  // Page numbers (show only 4)
  for (let i = start; i < end; i++) {
    const pageItem = paginationData[i];
    const pageUrl = `${baseUrl}${pageItem.id}`;
    const listItem = document.createElement("li");
    listItem.className = pageItem.id === caseId ? "bb-single-case active" : "bb-single-case";
    listItem.innerHTML = `<a href="${pageUrl}">${pageItem.caseNumber}</a>`;
    paginationList.appendChild(listItem);
  }

  // Next button
  if (hasNext) {
    const nextPageId = paginationData[currentPageIndex + 1].id;
    const nextItem = document.createElement("li");
    nextItem.innerHTML = `<a href="${baseUrl}${nextPageId}">Next</a>`;
    paginationList.appendChild(nextItem);
  } 
}

let filterBtn = document.querySelector(".bb-filter-heading")
let filterContent = document.querySelector(".bb-filter-content")

document.addEventListener('DOMContentLoaded', () => {
  if (filterBtn) {
    filterBtn.addEventListener("click", () => {
      filterContent.classList.toggle("active")
    })
  }

  const clickables = document.getElementsByClassName("toggle-on-click");

  if (clickables.length >= 1) {
    const clickablesArr = Array.from(clickables);
    clickablesArr.forEach(item => {
      const allClasses = item.classList;
      const toggleClasses = [];
      allClasses.forEach(classname => {
        if (classname.startsWith("toggle-")) {
          toggleClasses.push(classname.slice(7)); 
        }
      });

      item.addEventListener("click", () => {
        toggleClasses.forEach(classname => {
          const divToToggle = document.getElementsByClassName(classname)[0];
          if (divToToggle) {
            divToToggle.classList.toggle("isActive");
          }
        });
        item.classList.toggle("isActive");
      });
    });
  }

  const clickables2 = document.getElementsByClassName("toggle-on-click-multiple");
  const clickables2Close = document.getElementsByClassName("toggle-on-click-multiple-close");

  if (clickables2.length >= 1) {
    const clickablesArr = Array.from(clickables2);

    const clickablesArrClose = Array.from(clickables2Close)
    clickablesArrClose.forEach(item => {
      const allClasses = item.classList;
      const toggleClasses = [];
      allClasses.forEach(classname => {
        if (classname.startsWith("toggle-")) {
          toggleClasses.push(classname.slice(7)); 
        }
      });

      // Add click event listener to toggle the classes
      item.addEventListener("click", () => {
        toggleClasses.forEach(classname => {
          const divToToggle = document.getElementsByClassName(classname)[0];

          if (divToToggle) {
            divToToggle.classList.remove("isActive");
          }
        });
        // Toggle "isActive" class on the clicked item
        clickablesArr.forEach(item => item.classList.remove("isActive"))
        item.classList.toggle("isActive");

      });


    });

  }


})
let bb_acc = Array.from(document.querySelectorAll(".bb-accordion"));

function closeAllPanels() {
  for (let i = 0; i < bb_acc.length; i++) {
    bb_acc[i].classList.remove("active");
    let panel = bb_acc[i].nextElementSibling;
    panel.style.maxHeight = null;
  }
}

for (let i = 0; i < bb_acc.length; i++) {
  bb_acc[i].addEventListener("click", function () {
    if (!this.classList.contains("active")) {
      closeAllPanels();
    }
    this.classList.toggle("active");
    let panel = this.nextElementSibling;
    if (panel.style.maxHeight) {
      panel.style.maxHeight = null;
    } else {
      panel.style.maxHeight = panel.scrollHeight + "px";
    }
  });
}

jQuery(document).ready(function ($) {
  // SLIDER
  if ($.fn.slick) {
    const bb_slider = $('body .bb-slider').slick({
      // 	   $('body .bb-slider').slick({
      infinite: true,
      slidesToShow: 3,
      slidesToScroll: 1,
      prevArrow: `<img class="bb-arrow bb-left-arrow" src="${bb_plugin_data.leftArrow}">`,
      nextArrow: `<img class="bb-arrow bb-right-arrow" src="${bb_plugin_data.rightArrow}">`,
      responsive: [{
        breakpoint: 1200,
        settings: {
          slidesToShow: 2,
        }
      },
      {
        breakpoint: 992,
        settings: {
          slidesToShow: 1,
        }
      },
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 1,
          prevArrow: `<img class="bb-arrow bb-left-arrow" src="${bb_plugin_data.leftArrowUrl}">`,
          nextArrow: `<img class="bb-arrow bb-right-arrow" src="${bb_plugin_data.rightArrowUrl}">`,
        }
      }
      ]
    });
  } else {
    console.error("Slick Carousel is not loaded.");
  }
  const pageContainer = document.querySelector('body');

  const pageContainerWidth = pageContainer.offsetWidth;

  if (pageContainerWidth <= 1200 && pageContainerWidth >= 768) {
    bb_slider.slick('slickSetOption', 'slidesToShow', 2, true);
  } else if (pageContainerWidth <= 768) {
    bb_slider.slick('slickSetOption', 'slidesToShow', 1, true);
    bb_slider.slick('slickSetOption', 'prevArrow', `<img class="bb-arrow bb-left-arrow" src="${bb_plugin_data.leftArrowUrl}">`, true);
    bb_slider.slick('slickSetOption', 'nextArrow', `<img class="bb-arrow bb-right-arrow" src="${bb_plugin_data.rightArrowUrl}">`, true);
  }
  $(document).on('click', '#bb_update_api', function (e) {
    e.preventDefault();
    $('.update-api-status').text('');

    $.ajax({
      url: bb_plugin_data.ajaxurl,
      method: 'POST',
      data: {
        action: 'bb_update_api',
      },
      beforeSend: function () {
        $('.update-api').text('Loading...');
        $(this).prop('disabled', true);
      },
      success: function (response) {
        if (response.success) {
          $('.update-api').text('');
          $('.update-api-status').text('API Updated Successfully!');
          setTimeout(function () {
            $('.update-api-status').text('');
          }, 10000);
        } else {
          $('.update-api').text('');
          $('.update-api-status').text('API Update Failed!');
          setTimeout(function () {
            $('.update-api-status').text('');
          }, 10000);
        }
      },
      error: function () {
        alert('An error occurred while loading more items.');
        button.text('View More').prop('disabled', false);
        $('.update-api-status').text('Error occurred while updating API.');
      }
    });
  });

  $(document).on('click', '.ajax-load-more-btn', function (e) {
    e.preventDefault();

    var button = $(this);
    var offset = button.data('offset');
    var items_per_page = 10;

    // Make the AJAX request
    $.ajax({
      url: bb_plugin_data.ajaxurl,
      method: 'POST',
      data: {
        action: 'load_more_procedures',
        offset: offset,
        items_per_page: items_per_page
      },
      beforeSend: function () {
        button.text('Loading...').prop('disabled', true);
      },
      success: function (response) {
        if (response.success) {
          $('.bb-content-boxes>.ajax-load-more').before(response.data.items_html);

          button.data('offset', offset + items_per_page);

          if (!response.data.has_more) {
            button.hide();
          } else {
            button.text('View More').prop('disabled', false);
          }
        }
      },
      error: function () {
        alert('An error occurred while loading more items.');
        button.text('View More').prop('disabled', false);
      }
    });
  });

  $('.age-validation-modal').hide();
  $('.over_181').click(function () {
    $('.age-validation-modal').hide();
  })

  // category active 
  function getCatTitleFromUrl() {
    var catTitle = null;
    var path = window.location.pathname;
    var regex = /\/cat_title\/([^\/]+)\/?$/;
    var bb_cat_match = path.match(regex);

    if (bb_cat_match && bb_cat_match[1]) {
      catTitle = decodeURIComponent(bb_cat_match[1]);
    }
    if (!catTitle) {
      catTitle = 'Face';
    }
    return catTitle;
  }

  function activateAccordionAndPanel(catTitle) {
    if (!catTitle) return;

    var accordions = document.querySelectorAll('.bb-accordion');

    $('.bb-panel ul li a').each(function () {
      var accordion_anchor = $(this).closest('.bb-panel').prev('.bb-accordion');
      var cat_attr = accordion_anchor.attr('cat_title');
      var procedrue_path = window.location.pathname;
      if (cat_attr === catTitle && procedrue_path.split('/')[1] !== 'procedure-case') {
        var accordionButton = $(this).closest('.bb-panel').prev('.bb-accordion');
        accordionButton.addClass('active');
        accordionButton.next('.bb-panel').slideDown();
        var panel = accordionButton.next('.bb-panel')[0]; // Get the DOM element
        if (panel) {
          panel.style.maxHeight = panel.scrollHeight + "px";
        }
      }
    });
  }

  var _catTitle = getCatTitleFromUrl();
  if (_catTitle) {
    // activateAccordionAndPanel(_catTitle);
  }

  // Function to check and highlight matching procedure_id
  function highlightMatchingIds() {
    // Loop through each anchor tag
    var bb_c = 0;
    $('.bb-panel ul li a').each(function () {
      // Extract text excluding <span> content
      var bragbook_procedure_text = $(this).contents().filter(function () {
        return this.nodeType === Node.TEXT_NODE;
      }).text().trim();

      // Remove trailing hyphens
      bragbook_procedure_text = bragbook_procedure_text.replace(/-/g, ' ');
      bragbook_procedure_text = bragbook_procedure_text.toLowerCase();
      var currentUrl = window.location.href;
      var href = currentUrl;
      var procedureNameFromUrl = getProcedureNameFromUrl(href);
      procedureNameFromUrl = procedureNameFromUrl.toLowerCase();
      if (procedureNameFromUrl === null && bragbook_procedure_text !== null) {

        bb_c++;

      }

      if (bragbook_procedure_text === procedureNameFromUrl && bb_c <= 1) {

        // Add style to highlight the id
        $(this).css('color', '#000');
        $(this).css('opacity', '1');
        // Open the corresponding accordion panel
        var accordionButton = $(this).closest('.bb-panel').prev('.bb-accordion');
        accordionButton.addClass('active');
        accordionButton.next('.bb-panel').slideDown();
        var panel = accordionButton.next('.bb-panel')[0]; // Get the DOM element
        if (panel) {
          panel.style.maxHeight = panel.scrollHeight + "px";
        }

      }
    });
  }

  // // Helper function to extract procedure_id from URL
  function getProcedureNameFromUrl(url) {
    // Parse the URL to get the pathname 
    var pathname = new URL(url).pathname;
    var pathParts = pathname.split('/').filter(Boolean);

    // Check if the second-to-last segment is the one we're interested in
    var procedureSegment = pathParts.length > 2 ? pathParts[pathParts.length - 2] : pathParts[pathParts.length - 1];

    // Replace dashes with spaces
    var cleanedText = procedureSegment ? procedureSegment.replace(/-/g, ' ') : '';

    return cleanedText;
  }
  // Call the function to highlight matching ids when the page loads
  //highlightMatchingIds();

  var $pagination = $('.pagination');
  var currentPage = $pagination.data('current-page');
  var totalPages = $pagination.data('total-pages');

  // Function to update pagination buttons
  function updatePaginationButtons(currentPage, totalPages) {
    // Hide/show Previous button based on current page
    $('.load-more-btn.prev').toggle(currentPage > 1);

    // Hide/show Next button based on current page
    $('.load-more-btn.next').toggle(currentPage < totalPages);

    // Hide/show page numbers based on total pages
    $('.page-number').toggle(totalPages > 1);

    // Show current page and its neighbors
    $('.page-number').each(function () {
      var page = parseInt($(this).data('page'));
      $(this).toggle(page === currentPage || page === currentPage - 1 || page === currentPage + 1);
    });
  }

  // Initial setup
  updatePaginationButtons(currentPage, totalPages);

  // Click event for Previous and Next buttons
  $(document).on('click', '.load-more-btn', function (e) {
    e.preventDefault();

    var $this = $(this);
    var currentPage = $this.data('page');
    var nextPage = $this.hasClass('next') ? currentPage + 1 : currentPage - 1;

    $.ajax({
      url: bb_plugin_data.ajaxurl,
      type: 'post',
      dataType: 'html',
      data: {
        action: 'load_form_entries',
        page: nextPage
      },
      beforeSend: function () {
        $this.prop('disabled', true).text('Loading...');
      },
      success: function (response) {
        $('.form-entries-table tbody').html(response);

        // Highlight the current page number
        $('.page-number.active').removeClass('active');
        $('.page-number[data-page="' + nextPage + '"]').addClass('active');

        // Update data-page attribute of buttons
        $('.load-more-btn').data('page', nextPage);

        // Update pagination buttons visibility
        updatePaginationButtons(nextPage, totalPages);
      },
      complete: function () {
        $this.prop('disabled', false).text($this.hasClass('next') ? 'Next' : 'Previous');
      },
      error: function (xhr, status, error) {
        console.error(error);
        $this.prop('disabled', false).text($this.hasClass('next') ? 'Next' : 'Previous');
      }
    });

  });

  // Click event for page numbers
  $(document).on('click', '.page-number', function (e) {
    e.preventDefault();

    var $this = $(this);
    var currentPage = $this.data('page');

    $.ajax({
      url: bb_plugin_data.ajaxurl,
      type: 'post',
      dataType: 'html',
      data: {
        action: 'load_form_entries',
        page: currentPage
      },
      beforeSend: function () {
        $('.load-more-btn').prop('disabled', true).text('Loading...');
      },
      success: function (response) {
        $('.form-entries-table tbody').html(response);

        // Highlight the current page number
        $('.page-number.active').removeClass('active');
        $this.addClass('active');

        // Update data-page attribute of buttons
        $('.load-more-btn').data('page', currentPage);

        // Update pagination buttons visibility
        updatePaginationButtons(currentPage, totalPages);
      },
      complete: function () {
        $('.load-more-btn').prop('disabled', false);
      },
      error: function (xhr, status, error) {
        console.error(error);
        $('.load-more-btn').prop('disabled', false);
      }
    });
  });

  // ajax request for button

  $('.bb-form').submit(function (event) {
    // Prevent default form submission
    event.preventDefault();

    // Get form data
    var formData = $(this).serialize();

    // AJAX request
    $.ajax({
      type: 'POST',
      url: bb_plugin_data.ajaxurl, // WordPress AJAX URL
      data: formData + '&action=handle_form_submission', // Add action parameter
      beforeSend: function () {
        $(".bb-is-required-success").text('Submitting form...');
      },
      success: function (response) {
        var successMessage = response.data;
        $(".bb-is-required-success").text(successMessage);
        // $(".bb-consultation-form").addClass("bb-display-none");

      },
      error: function (xhr, status, error) {
        // Handle error
        $(".bb-is-required-success").text(error);
      }
    });
  });


  // Check if any form exists in the bb-main area
  if ($(".bb-main .bb-form").length) {
    var $bb_form = $(".bb-main .bb-form");
    var $bb_inputs = $(".bb-main .bb-is-required");

    $bb_form.on("submit", function (e) {
      var bb_is_required = true;

      $bb_inputs.each(function () {
        if ($(this).val() === "") {
          bb_is_required = false;
          $(this).next().show();
        } else {
          $(this).next().hide();
        }
      });

      if (!bb_is_required) {
        e.preventDefault();
      } else {
        $(".bb-is-required-success").show();
      }
    });
  }

  // Toggle functionality for both sidebar toggle buttons
  $(".bb-sidebar-toggle").on("click", function () {
    // Toggle the sidebar visibility
    $(".bb-sidebar").toggleClass("active"); // Example to add a class
    $(this).toggleClass("active"); // Toggle the active class for the button
  });

  // Intercept the form submission
  $('#bragbook_setting_page').on('submit', function (e) {
    e.preventDefault(); // Prevent the default form submission
    $('.bb-save-api-settings-status').text('');
    const dataToSend = [];
    const inputs = document.querySelectorAll('input[name^="bb_gallery_page_slug"]');
    inputs.forEach((input) => {
      dataToSend.push({
        key: input.dataset.key,
        value: input.value
      });
    });
    // Collect form data
    var formData = $(this).serialize();

    // Send AJAX request
    $.ajax({
      url: bb_plugin_data.ajaxurl, // WordPress AJAX URL
      type: 'POST',
      data: {
        action: 'bb_save_bragbook_settings', // Action to trigger on server-side
        form_data: formData, // Pass serialized form data
        bb_page_keys: dataToSend
      },
      beforeSend: function () {
        $('.bb-save-api-status').text('Loading...');
        $(this).prop('disabled', true);
      },
      success: function (response) {
        if (response.success) {
          $('.bb-save-api-status').text('');
          $('.bb-save-api-settings-status').text('Settings saved successfully.');
          setTimeout(function () {
            $('.bb-save-api-settings-status').text('');
          }, 10000);
        } else {
          $('.bb-save-api-status').text('');
          $('.bb-save-api-settings-status').text('There was an error saving the settings.');
          setTimeout(function () {
            $('.bb-save-api-settings-status').text('');
          }, 10000);
        }
      },
      error: function () {
        alert('AJAX request failed.');
      }
    });
  });
  // Prevent page reload when clicking the submit button directly
  $('#bragbook_seeting_form').find('input[type="submit"]').on('click', function (e) {
    e.preventDefault();
    $('#bragbook_seeting_form').trigger('submit');
  });
});

setTimeout(() => {
  const modalToggle = Array.from(document.querySelectorAll(".bb-open-fav-modal"));
  modal = document.querySelector(".bb-fav-modal");
  modalInner = document.querySelector(".bb-fav-modal-inner");
  if (modalInner && modalInner.querySelector) {
    form = modalInner.querySelector("form");
    caseIdInput = modalInner.querySelector("input[name='case-id']");
    bbApiTokenInput = modalInner.querySelector("input[name='api-token']");
    bbWebsiteIdInput = modalInner.querySelector("input[name='website-id']");
  }

  function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
  }


  // Function to open the modal
  function openModal(caseId, bbApiToken, bbWebsiteId) {
    // Set the caseId value in the form   
    if (caseIdInput) {
      caseIdInput.value = caseId;
    }
    if (bbApiTokenInput) {
      bbApiTokenInput.value = bbApiToken;
    }
    if (bbWebsiteIdInput) {
      bbWebsiteIdInput.value = bbWebsiteId;
    }

    var encodedCookieValue = getCookie('wordpress_favorite_email');
    if (encodedCookieValue !== undefined) {

      var bb_favorite_email = decodeURIComponent(encodedCookieValue);
      var bb_favorite_name = getCookie('wordpress_favorite_name');
      var bb_favorite_name = decodeURIComponent(bb_favorite_name);

      var bb_favorite_phone = getCookie('wordpress_favorite_phone');
      var bb_favorite_phone = decodeURIComponent(bb_favorite_phone);

      var bb_favorite_case_id = getCookie('wordpress_favorite_case_id');
      var bb_favorite_case_id = decodeURIComponent(bb_favorite_case_id);
      var caseId = Number(caseId);

      var bb_favorite_api_token = getCookie('wordpress_favorite_api_token');
      var bb_favorite_api_token = decodeURIComponent(bb_favorite_api_token);

      var bb_favorite_website_id = getCookie('wordpress_favorite_website_id');
      var bb_favorite_website_id = decodeURIComponent(bb_favorite_website_id);

      var bb_fav_list_cookie = bb_favorite_case_id.split(',').map(Number);
      var bb_exist_list = new Set(bb_fav_list_cookie);
      var bb_exist = bb_exist_list.has(caseId);

      if (bb_exist) {
        alert('Already favorite!');
        return false;
      } else {
        var data_cookie = {
          email: bb_favorite_email,
          phone: bb_favorite_phone,
          name: bb_favorite_name,
          caseIds: [caseId],
          bbApiTokens: [bbApiToken],
          bbWebsiteIds: [bbWebsiteId],
        };
        bb_favorites_submission(data_cookie);
      }

    } else {
      fadeIn(modal);
    }

  }

  // Function to close the modal
  function closeModal() {
    fadeOut(modal);
  }

  // Add event listeners for modal toggles
  if (modalToggle) {
    modalToggle.forEach(toggle => {
      toggle.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent the click event from propagating

        const caseId = toggle.getAttribute('data-case-id');
        const bbApiToken = toggle.getAttribute('data-bb_api_token');
        const bbWebsiteId = toggle.getAttribute('data-bb_website_id');
        openModal(caseId, bbApiToken, bbWebsiteId);
        //      openModal(caseId);
      });
    });

    // Close the modal when clicking outside the modal content
    document.addEventListener("click", (event) => {
      if (modal && modal.classList.contains('is-open') && !modalInner.contains(event.target)) {
        closeModal();
      }
    });

    function bb_favorites_submission(data) {
      // Submit form with caseId
      var caseId = data.caseIds;
      jQuery.ajax({
        url: bb_plugin_data.ajaxurl,
        type: 'POST',
        data: {
          action: 'bragbook_my_favorite',
          email: data.email,
          phone: data.phone,
          name: data.name,
          caseIds: data.caseIds,
          bbApiTokens: data.bbApiTokens,
          bbWebsiteIds: data.bbWebsiteIds,

        },
        success: function (response) {
          if (response.success) {
            var imgElement = jQuery(`img[data-case-id="${caseId}"]`);
            if (imgElement.length) {
              // Replace the src attribute
              imgElement.each(function () {
                jQuery(this).attr('src', bb_plugin_data.heartBordered);

              });
            }
            // Select the <span> element and get its current text value
            var $span = jQuery('a.bb-sidebar_favorites span');
            var text = $span.text();
            // Extract the number between parentheses
            var match = text.match(/\((\d+)\)/);
            if (match) {
              var currentValue = parseInt(match[1], 10);

              // Increment the value
              var newValue = currentValue + 1;

              // Update the <span> element with the new value, keeping parentheses
              $span.text('(' + newValue + ')');
            }
            closeModal();


          } else {
            alert('Failed to save favorite.');
            closeModal();

          }
        },
        error: function (error) {
          console.error('Error:', error);
          // alert('An error occurred.');
          closeModal();

        }
      });
    }
    if (modalInner && modalInner.querySelector) {
      // Add event listener for form submission
      form.addEventListener("submit", (e) => {
        e.preventDefault(); // Prevent default form submission

        // Get the case-id value from the input
        const caseId = caseIdInput.value;
        const bbApiToken = bbApiTokenInput.value;
        const bbWebsiteId = bbWebsiteIdInput.value;
        var formData = new FormData(form);
        var data = {
          email: formData.get('email'),
          phone: formData.get('number'),
          name: formData.get('name'),
          caseIds: [caseId],
          bbApiTokens: [bbApiToken],
          bbWebsiteIds: [bbWebsiteId],
        };
        bb_favorites_submission(data);
      });
    }
    // Fade in and out functions
    function fadeOut(element) {
      var opacity = 1;

      function decrease() {
        opacity -= 0.05;
        if (opacity <= 0) {
          element.style.opacity = 0;
          element.classList.remove('is-open');
          return true;
        }
        element.style.opacity = opacity;
        requestAnimationFrame(decrease);
      }
      decrease();
    }

    function fadeIn(element) {
      var opacity = 0;
      element.classList.add('is-open');

      function increase() {
        opacity += 0.05;
        if (opacity >= 1) {
          element.style.opacity = 1;
          return true;
        }
        element.style.opacity = opacity;
        requestAnimationFrame(increase);
      }
      increase();
    }
  }
}, 2000);

Array.from(document.querySelectorAll(".bb-filter-select")).forEach(filter => {
  if (filter) {
    filter.querySelector(".bb-filter-heading")?.addEventListener("click", () => filter?.classList.toggle("active"))
  }
})
Array.from(document.querySelectorAll(".bb-filter-toggle")).forEach(filter => {
  if (filter) {
    filter.addEventListener("click", () => document.querySelector(".bb-filter-select").classList.toggle("active"))
  }
})




//consultation form validation
if (document.querySelector(".bb-main .bb-form")) {
  let bb_form = document.querySelector(".bb-main .bb-form");
  let bb_inputs = Array.from(document.querySelectorAll(".bb-main .bb-is-required"));
  bb_form.addEventListener("submit", (e) => {
    let bb_is_required = true;
    bb_inputs.forEach(input => {
      if (input.value === "") {
        bb_is_required = false;
        input.nextElementSibling.style.display = "block";
      } else {
        input.nextElementSibling.style.display = "none";
      }
    });
    if (!bb_is_required) {
      e.preventDefault();
    } else {
      document.querySelector(".bb-is-required-success").style.display = "block";
    }
  });
}

setTimeout(() => {
  // Get modal elements
  const bbrag_modal = document.getElementById('bbrag_modal');
  const bbrag_modalImage = document.getElementById('bbrag_modalImage');
  const bbrag_closeModal = document.querySelector('.bbrag_close');
  const bbrag_prevArrow = document.querySelector('.bbrag_prev');
  const bbrag_nextArrow = document.querySelector('.bbrag_next');

  let bbrag_currentIndex = 0;
  const bbrag_images = document.querySelectorAll('.bbrag_gallery_image');
  // Function to open the modal and display the selected image
  function bbrag_openModal(index) {
    bbrag_currentIndex = index;
    bbrag_modal.style.display = 'block';
    bbrag_modalImage.src = bbrag_images[bbrag_currentIndex].src;
  }

  // Function to close the modal
  function bbrag_closeModalHandler() {
    bbrag_modal.style.display = 'none';
  }

  // Function to show the next image
  function bbrag_showNextImage() {
    bbrag_currentIndex = (bbrag_currentIndex + 1) % bbrag_images.length;
    bbrag_modalImage.src = bbrag_images[bbrag_currentIndex].src;
  }

  // Function to show the previous image
  function bbrag_showPrevImage() {
    bbrag_currentIndex = (bbrag_currentIndex - 1 + bbrag_images.length) % bbrag_images.length;
    bbrag_modalImage.src = bbrag_images[bbrag_currentIndex].src;
  }

  // Add click event listeners to images
  bbrag_images.forEach((img, index) => {
    img.addEventListener('click', () => bbrag_openModal(index));
  });

  // Add click event listeners to modal controls
  if (bbrag_closeModal) {
    bbrag_closeModal.addEventListener('click', bbrag_closeModalHandler);
  }
  if (bbrag_prevArrow) {
    bbrag_prevArrow.addEventListener('click', bbrag_showPrevImage);
  }
  if (bbrag_nextArrow) {
    bbrag_nextArrow.addEventListener('click', bbrag_showNextImage);
  }

  // Close the modal if the user clicks outside of the image
  window.addEventListener('click', (event) => {
    if (event.target === bbrag_modal) {
      bbrag_closeModalHandler();
    }
  });
  
}, 2000);