jQuery(document).ready(function ($) {
  $("body").on("click", ".image-field button.upload", function (e) {
    e.preventDefault();
    media_input = $(this).parent().find("input");
    media_preview = $(this).parent().parent().find("img");
    if (!media_preview.length) {
      preview_el = document.createElement("img");
      $(this).parent().parent().append(preview_el);
      media_preview = $(this).parent().parent().find("img");
    }

    var button = $(this),
      custom_uploader = wp
        .media({
          title: baseplugin.upload_title,
          library: {
            uploadedTo: wp.media.view.settings.post.id,
            type: "image",
          },
          button: {
            text: baseplugin.upload_button, // button label text
          },
          multiple: false, // for multiple image selection set to true
        })
        .on("select", function () {
          // it also has "open" and "close" events
          var attachment = custom_uploader
            .state()
            .get("selection")
            .first()
            .toJSON();
          $(media_preview).attr("src", attachment.url);
          $(media_input).val(attachment.url);
        })
        .open();
  });

  $("body").on("click", ".image-field button.remove", function (e) {
    e.preventDefault();
    media_input = $(this).parent().find("input");
    media_preview = $(this).parent().parent().find("img");
    $(media_preview).remove();
    $(media_input).val("");
  });
});
