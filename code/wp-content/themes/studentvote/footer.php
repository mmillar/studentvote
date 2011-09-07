      <div id="footer">
        <?php wp_footer(); ?>
        <hr>
        <div class="themark_footer" onclick="http://themarknews.com"></div>
        <?php wp_nav_menu(array('menu' => 'footer_menu')); ?>
        <script type="text/javascript">
          $(function() {
          	$("#footer ul li:last-child").css("border-right","none");
          });
        </script>
      </div>
      
    </div>

  </body>
</html>