const registerForm = document.getElementById("registerForm");
const loginForm = document.getElementById("loginForm");
const authToast = document.getElementById("authToast");
//this is for registration page
if (registerForm) {
  registerForm.addEventListener("submit", function (e) {
    let isValid = true;

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const password = document.getElementById("password").value.trim();

    document.getElementById("nameError").innerText = "";
    document.getElementById("emailError").innerText = "";
    document.getElementById("phoneError").innerText = "";
    document.getElementById("passwordError").innerText = "";

    if (name === "") {
      document.getElementById("nameError").innerText = "Name is required!";
      isValid = false;
    }

    if (email === "") {
      document.getElementById("emailError").innerText = "Email is required!";
      isValid = false;
    }

    if (phone === "") {
      document.getElementById("phoneError").innerText = "Phone is required!";
      isValid = false;
    }

    if (password === "") {
      document.getElementById("passwordError").innerText =
        "Password is required!";
      isValid = false;
    }

    if (!isValid) e.preventDefault();
  });
}
//this is for login page
if (loginForm) {
  loginForm.addEventListener("submit", function (e) {
    let isValid = true;

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    document.getElementById("emailError").innerText = "";
    document.getElementById("passwordError").innerText = "";

    if (email === "") {
      document.getElementById("emailError").innerText = "Email is required!";
      isValid = false;
    }

    if (password === "") {
      document.getElementById("passwordError").innerText =
        "Password is required!";
      isValid = false;
    }

    if (!isValid) e.preventDefault();
  });
}
//toaster
if (authToast && typeof bootstrap !== "undefined") {
  bootstrap.Toast.getOrCreateInstance(authToast).show();
}
//category data table 
$(document).ready(function () {
  var table = $("#categoryDataTable").DataTable({
    ajax: {
      url: "allcategory.php?ajax=1",
      data: function (d) {
        d.status = $('select[name="status"]').val();
      },
      dataSrc: function (json) {
        if (!json || typeof json !== "object" || !("data" in json)) {
          console.warn("Unexpected response from allcategory.php?ajax=1", json);
          return [];
        }
        return json.data || [];
      },
      error: function (xhr, status, error) {
        console.error("Failed to load categories:", status, error);
      },
    },

    pageLength: 10,
    order: [[1, "asc"]],

    columns: [
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row, meta) {
          return meta.row + meta.settings._iDisplayStart + 1;
        },
      },
      { data: "name" },
      { data: "slug" },
      {
        data: "status",
        render: function (status) {
          const statusValue = String(status).toLowerCase().trim();
          return (
            '<span class="status-badge status-' +
            statusValue +
            '">' +
            statusValue +
            "</span>"
          );
        },
      },
      {
        data: "id",
        orderable: false,
        searchable: false,
        render: function (id) {
          return `
            <button type="button" class="btn btn-primary btn-sm edit" data-id="${id}">Edit</button>
            <button type="button" class="btn btn-danger btn-sm delete" data-id="${id}">Delete</button>
          `;
        },
      },
    ],
  });

  // Status filter with clear button (category page)
  $('#statusFilter, select[name="status"]').on("change", function () {
    var table = $("#categoryDataTable").DataTable();
    var $this = $(this);
    var val = $this.val();

    // In markup, the clear button is usually inside the same .position-relative wrapper.
    var $clearBtn = $this.closest(".position-relative").find(".btn-clear-filter");
    if ($clearBtn.length) {
      if (val && val !== "") $clearBtn.show();
      else $clearBtn.hide();
    }

    table.ajax.reload();
  });


  $(document).on("click", ".btn-clear-filter", function (e) {
    e.preventDefault();
    var $select = $(this).closest(".position-relative").find("select");
    $select.val("").trigger("change");
  });
  // Set initial clear-button state.
  if ($("#statusFilter").length) {
    $("#statusFilter").trigger("change");
  }
});

//this is for add category modal
$(document).on("click", "#addBtn", function (e) {
  e.preventDefault();

  $.ajax({
    url: "addcategory.php",
    type: "GET",
    data: { modal: 1 },
    success: function (res) {
      $("#modalContent").html(res);
      if (typeof enableCategoryCustomValidation === "function") {
        enableCategoryCustomValidation();
      }
      if (typeof bootstrap !== "undefined") {
        new bootstrap.Modal(document.getElementById("myModal")).show();
      }
    },
    error: function () {
      Swal.fire("Error", "Unable to open Add Category modal.", "error");
    },
  });
});
// Clears validation error messages from category forms
function clearValidationErrors(form) {
  var $form = $(form);
  $form.find("#categoryError").text("");
  $form.find("#statusError").text("");
}


// this is for category validation
// Validates required fields for category forms (name and status)
function validateRequiredFields(form) {
  let isValid = true;
  let $form = $(form);

  let name = String($form.find("[name='name']").val() || "").trim();
  let status = String($form.find("[name='status']").val() || "").trim();

  clearValidationErrors(form);

  if (name === "") {
    $form.find("#categoryError").text("Category name is required!");
    isValid = false;
  }

  if (status === "") {
    $form.find("#statusError").text("Status is required!");
    isValid = false;
  }

  return isValid;
}



$(document).on("submit", "#addForm", function (e) {
  e.preventDefault();

  let form = this;
  let formData = $(form).serialize();

  if (!validateRequiredFields(form)) return;

  $.ajax({
    url: "addcategory.php",
    type: "POST",
    data: formData,
    dataType: "json",
    success: function (data) {
      if (!data.success) {
        $(form).find("#categoryError").text(data.errors?.name || data.errors?.general || "");
        $(form).find("#statusError").text(data.errors?.status || "");
        return;
      }

      clearValidationErrors(form);

      let modal = bootstrap.Modal.getInstance(
        document.getElementById("myModal"),
      );
      modal.hide();

      form.reset();

      $("#categoryDataTable").DataTable().ajax.reload();
    },
  });
});

$(document).on("click", ".edit", function (e) {
  e.preventDefault();

  let id = $(this).data("id");

  $.get("editcategory.php", { modal: 1, id: id }, function (res) {
    $("#modalContent").html(res);
    if (typeof enableCategoryCustomValidation === "function") {
      enableCategoryCustomValidation();
    }
    if (typeof bootstrap !== "undefined") {
      new bootstrap.Modal(document.getElementById("myModal")).show();
    }
  });
});

$(document).on("click", ".delete", function (e) {
  e.preventDefault();
  const id = $(this).data("id");

  Swal.fire({
    title: "Delete this category?",
    text: "This action cannot be undone.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "Cancel",
  }).then((result) => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: "deletecategory.php",
      type: "POST",
      data: { id: id },
      dataType: "text",
      success: function (responseText) {
        let response;
        try {
          response = JSON.parse(responseText);
        } catch (e) {
          const cleaned = responseText.trim();
          if (cleaned.toLowerCase().includes("category deleted")) {
            Swal.fire("Deleted!", "Category deleted successfully.", "success");
            $("#categoryDataTable").DataTable().ajax.reload();
            return;
          }
          console.error("Unexpected delete response", responseText);
          Swal.fire("Error", "Could not parse server response.", "error");
          return;
        }

        if (response && response.success) {
          Swal.fire(
            "Deleted!",
            response.message || "Category deleted.",
            "success",
          );
          $("#categoryDataTable").DataTable().ajax.reload();
        } else {
          Swal.fire(
            "Error",
            (response && response.message) || "Could not delete category.",
            "error",
          );
        }
      },
      error: function (xhr, status, error) {
        console.error(
          "Server error while deleting category:",
          status,
          error,
          xhr.responseText,
        );
        Swal.fire("Error", "Server error while deleting category.", "error");
      },
    });
  });
});

$(document).on("submit", "#editForm, #editCategoryForm", function (e) {
  e.preventDefault();

  let form = this;
  let formData = $(form).serialize();

  if (!validateRequiredFields(form)) return;

  $.ajax({
    url: "editcategory.php",
    type: "POST",
    data: formData,
    dataType: "json",
    success: function (data) {
      if (!data.success) {
        $(form).find("#categoryError").text((data.errors && (data.errors.name || data.errors.general)) || "");
        $(form).find("#statusError").text((data.errors && data.errors.status) || "");
        return;
      }

      let modalEl = document.getElementById("myModal");
      let modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
      if (modal) {
        modal.hide();
      } else {
        window.location.href = "/blog/admin/category/allcategory.php";
        return;
      }

      $("#categoryDataTable").DataTable().ajax.reload();
    },
  });
});

// Gets or creates error display element for post form fields
function getOrCreatePostError($field, errorId) {
  let $error = $("#" + errorId);
  if ($error.length) return $error;

  $error = $('<span class="text-danger d-block mt-1"></span>').attr("id", errorId);

  
  const $group = $field.closest(".col-md-6, .col-12, .mb-3, .mb-4");
  if ($group.length) {
    $group.append($error);
  } else if ($field.next(".select2").length) {
    $field.next(".select2").after($error);
  } else if ($field.closest(".dropify-wrapper").length) {
    $field.closest(".dropify-wrapper").after($error);
  } else {
    $field.after($error);
  }

  return $error;
}

// this is for post form validations
// Validates all fields in post forms (title, content, image, etc.)
function validatePostForm(form) {
  const $form = $(form);
  const $title = $form.find("[name='title']");
  const $short = $form.find("[name='short_description']");
  const $content = $form.find("[name='content']");
  const $image = $form.find("[name='image']");
  const $status = $form.find("[name='status']");
  const $category = $form.find("[name='category']");

  const $titleError = getOrCreatePostError($title, "postTitleError");
  const $shortError = getOrCreatePostError($short, "postShortDescriptionError");
  const $contentError = getOrCreatePostError($content, "postContentError");
  const $imageError = getOrCreatePostError($image, "postImageError");
  const $statusError = getOrCreatePostError($status, "postStatusError");
  const $categoryError = getOrCreatePostError($category, "postCategoryError");

  $titleError.text("");
  $shortError.text("");
  $contentError.text("");
  $imageError.text("");
  $statusError.text("");
  $categoryError.text("");

  let isValid = true;

  if (String($title.val() || "").trim() === "") {
    $titleError.text("Title is required!");
    isValid = false;
  }

  if (String($short.val() || "").trim() === "") {
    $shortError.text("Short description is required!");
    isValid = false;
  }

  if (String($content.val() || "").replace(/<[^>]*>/g, "").trim() === "") {
    $contentError.text("Content is required!");
    isValid = false;
  }

  if (String($image.val() || "").trim() === "") {
    $imageError.text("Image is required!");
    isValid = false;
  }

  if (String($status.val() || "").trim() === "") {
    $statusError.text("Status is required!");
    isValid = false;
  }

  if (String($category.val() || "").trim() === "") {
    $categoryError.text("Category is required!");
    isValid = false;
  }

  return isValid;
}

$(document).on("submit", "form", function (e) {
  const $form = $(this);
  const isPostForm =
    $form.find("[name='title']").length &&
    $form.find("[name='short_description']").length &&
    $form.find("[name='content']").length &&
    $form.find("[name='image']").length &&
    $form.find("[name='status']").length &&
    $form.find("[name='category']").length;

  if (!isPostForm) return;

  this.noValidate = true;
  if (!validatePostForm(this)) {
    e.preventDefault();
  }
});

$(document).on(
  "input change",
  "form [name='title'], form [name='short_description'], form [name='content'], form [name='image'], form [name='status'], form [name='category']",
  function () {
    const $form = $(this).closest("form");
    const isPostForm =
      $form.find("[name='title']").length &&
      $form.find("[name='short_description']").length &&
      $form.find("[name='content']").length &&
      $form.find("[name='image']").length &&
      $form.find("[name='status']").length &&
      $form.find("[name='category']").length;

    if (isPostForm) {
      validatePostForm($form[0]);
    }
  }
);




$(document).ready(function () {
  
  $("#content").summernote({
    height: 300,
    placeholder: "Write your full blog content here...",
    toolbar: [
      ["style", ["style"]],
      ["font", ["bold", "italic", "underline", "clear"]],
      ["fontname", ["fontname"]],
      ["color", ["color"]],
      ["para", ["ul", "ol", "paragraph"]],
      ["insert", ["link", "picture", "video"]],
      ["view", ["fullscreen", "codeview", "help"]],
    ],
    callbacks: {
      onChange: function (contents, $editable) {
        $("#content").val(contents);
      },
    },
  });

  
  $(".dropify").dropify();

 
  if ($("#category").length) {
    $("#category").select2({
      theme: "bootstrap-5",
      width: "100%",
      placeholder: "Select a category",
      minimumResultsForSearch: 0,
      allowClear: false,
      dropdownParent: $(".custom-card"),
    });
  }

  // make sure status remains a native select for consistent behavior
  if ($("#status").hasClass("select2-hidden-accessible")) {
    $("#status").select2("destroy");
    $("#status").removeClass("select2-hidden-accessible");
  }
});

// show generated toast if it exists
document.addEventListener("DOMContentLoaded", function () {
  var toastEl = document.getElementById("postToast");
  if (toastEl) {
    var bsToast = new bootstrap.Toast(toastEl);
    bsToast.show();
  }
});

// Posts DataTable (AJAX)
$(document).ready(function () {
  if (!$("#postsDataTable").length) return;
  if (!$.fn || !$.fn.DataTable) return;

  const $table = $("#postsDataTable");
  const hasActions =
    $table
      .find("thead th")
      .toArray()
      .some((th) => $(th).text().trim().toLowerCase() === "actions");

  const columns = [
    {
      data: null,
      orderable: false,
      searchable: false,
      render: function (data, type, row, meta) {
        return meta.row + meta.settings._iDisplayStart + 1;
      },
    },
    {
      data: "image",
      orderable: false,
      render: function (image, type, row) {
        if (!image) return '<span class="text-muted">No Image</span>';

        const fileName = String(image).replace(/^.*[\\\/]/, "");
        return `<img src="/blog/assets/posts/${fileName}" alt="Post image" style="width: 80px; height: 50px; object-fit: cover; border-radius: 8px;" />`;
      },
    },
    { data: "title" },
    { data: "category" },
    { data: "status" },
    { data: "createdby" },
    { data: "posted" },
  ];

  if (hasActions) {
    columns.push({
      data: "id",
      orderable: false,
      searchable: false,
      render: function (id, type, row) {
        return `
          <a href="/blog/admin/post/editpost.php?id=${id}" class="btn btn-sm btn-primary">Edit</a>
          <button type="button" class="btn btn-sm btn-danger delete-post" data-id="${id}">Delete</button>
        `;
      },
    });
  }
  const table = $table.DataTable({
    ajax: {
      url: "allpost.php?ajax=1",
      data: function (d) {
        const status = $("#statusFilter").length
          ? $("#statusFilter").val()
          : "";
        d.status = status || "";
      },
      dataSrc: function (json) {
        if (!json || typeof json !== "object" || !("data" in json)) {
          console.warn("Unexpected response from allpost.php?ajax=1", json);
          return [];
        }
        return json.data || [];
      },
    },
    pageLength: 10,
    order: [[2, "asc"]], 
    columns: columns,
    columnDefs: [{ targets: 1, width: "120px" }],
  });




  // Filter by status
  if ($("#statusFilter").length) {
    $("#statusFilter").on("change", function () {
      const hasValue = String($(this).val() || "").trim() !== "";
      const $clearBtn = $(".btn-clear-post-filter");
      if ($clearBtn.length) {
        $clearBtn.toggle(hasValue);
      }
      table.ajax.reload();
    });

    // Set initial clear-button state.
    $("#statusFilter").trigger("change");
  }

  $(document).on("click", ".btn-clear-post-filter", function (e) {
    e.preventDefault();
    $("#statusFilter").val("").trigger("change");
  });




  // Delete post
  $(document).on("click", ".delete-post", function (e) {
    e.preventDefault();
    const id = $(this).data("id");

    Swal.fire({
      title: "Delete this post?",
      text: "This action cannot be undone.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "Cancel",
    }).then((result) => {
      if (result.isConfirmed) {
        // Submit POST back to allpost.php so PHP delete logic runs.
        const $form = $(
          '<form method="POST" class="delete-post-form" style="display:none;"></form>'
        );
        $form.attr("action", "allpost.php");
        $form.append('<input type="hidden" name="delete_id" />');
        $form.find('input[name="delete_id"]').val(id);
        $("body").append($form);
        $form.submit();
      }
    });
  });
});

