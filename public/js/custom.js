$(document).ready(function(){
    $(".deletebtn").click(function (e) { 
        e.preventDefault();
        var delete_id = $(this).closest("tr").find(".delete_hidden_value").val();
        console.log(delete_id);
        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover this Invitation!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
                let data = {
                    "_token": $('meta[name="csrf-token"]').attr('content'),
                }
                $.ajax({
                    type: "DELETE",
                    url: invitation_deleteRoute.replace('delete_id', delete_id),
                    data: data,
                    success: function(response) {
                        swal(response.message, {
                            icon: "success",
                        }).then((result) => {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        swal("Error", "Something went wrong", "error");
                    }
                });
            }
          });
    });

    $(".deletebtn_template").click(function (e) { 
        e.preventDefault();
        var delete_id = $(this).closest("tr").find(".delete_hidden_value").val();
        console.log(delete_id);
        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover this Template!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
                let data = {
                    "_token": $('meta[name="csrf-token"]').attr('content'),
                }
                $.ajax({
                    type: "DELETE",
                    url: template_delete.replace('delete_id', delete_id),
                    data: data,
                    success: function(response) {
                        swal(response.message, {
                            icon: "success",
                        }).then((result) => {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        swal("Error", "Something went wrong", "error");
                    }
                });
            }
          });
    });
});