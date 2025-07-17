<?php
/*
Template Name: Case Details V2 Page Template
*/
?>
<div class="bbrag-case-detail bbrag-case-detail-v2 bb-main-v2">
  <?php
  include plugin_dir_path(__FILE__) . 'sidebar-template.php';
  ?>
  <h1 class="bbrag-case-title"></h1>
    <div class="bbrag-slider-wrapper">
      <div class="bbrag-main-image">
        <img id="bbrag-active-image" src="" alt="Main Image">
      </div>
      <div class="bbrag-thumbnails">
        <button class="bbrag-thumb-arrow-left">&#10094;</button>
        <div class="bbrag-thumbnail-track-wrapper">
          <div class="bbrag-thumbnail-track" id="bbrag-thumbnail-track">
          </div>
        </div>
        <button class="bbrag-thumb-arrow-right">&#10095;</button>
      </div>
    </div>
   

  <div class="case-detail-mobile-gallery" id="case-detail-mobile-gallery">
  </div>
  <div class="bbrag-content">
    
    <div class="bbrag-description">

    </div>

    <aside class="bbrag-sidebar">
      <h3>Demographics</h3>
      <ul class="bbrag-demographic-v2">

      </ul>
    </aside>
  </div>

  <!-- Procedure Details -->
  <div class="bbrag-procedure-table">
    <h3>Procedure Specific Details</h3>
    <div class="bbrag-technique"><strong>Technique:</strong> Rapid Recovery Breast Augmentation Saline Breast Implants
    </div>
    <div class="bbrag-grid"> </div>
  </div>

  <!-- Pagination -->
  <div class="bbrag-pagination">
  </div>
</div>

<?php
get_footer();