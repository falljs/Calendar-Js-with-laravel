<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Booking Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <style>
        .fc-event{
            width:140px;
            height: 85px;
            display:flex;
            flex-wrap:wrap;
            align-content:center;
        }
    </style>
</head>
<body>
    <!-- Modal -->
    <div class="modal fade" id="bookingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Add new Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="g-3">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <label for="title" class="visually-hidden">Title</label>
                            <input type="text" class="form-control" id="title" name="title">
                            <div id="titleError" class="text-danger"></div>
                        </div>
                    </div>
                    <!-- <div class="row" hidden>
                        <div class="col-md-6">
                            <label for="inputStartDate" class="visually-hidden">Start Date</label>
                            <input type="date" class="form-control" id="inputStartDate">
                        </div>
                        <div class="col-md-6">
                            <label for="inputStartDate" class="visually-hidden">Start Date</label>
                            <input type="date" class="form-control" id="inputStartDate">
                        </div>
                    </div> -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" id="addBooking" class="btn btn-primary">Enregistrer</button>
            </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mt-5">FullCalendar Js - Laravel</h3>
                <div class="col-md-11 offset-1 mt-5 mb-5">
                    <div id="calendar">

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var bookings = @json($events);
            $('#calendar').fullCalendar({
                header:{
                    'left':'prev, next today',
                    center: 'title',
                    right:'month, agendaWeek, agendaDay'
                },
                events: bookings, //passer les bookings au calendrier
                selectable:true,  // Rendre le calendrier cliquable
                selectHelper:true, // Rendre le calendrier cliquable
                defaultView:'month', // Laod calendar by default [month or agendaWeek or agendaDay]
                select: function(start, end, allDays){
                    $('#bookingModal').modal('toggle'); //open modal

                    $('#addBooking').one('click', function() {
                        var title = $('#title').val();
                        var start_date = moment(start).format('YYYY-MM-DD HH:mm');
                        var end_date = moment(end).format('YYYY-MM-DD HH:mm');
                        $.ajax({
                            url: "{{ route('calendar.store') }}",
                            type: "POST", // This must be POST
                            dataType: 'json',
                            data: { title, start_date, end_date },
                            success: function(response) {
                                //Fermeture du Modal
                                $('#bookingModal').modal('hide');
                                //Aperçu du book ajouté sans rafraîchir la page
                                $('#calendar').fullCalendar('renderEvent',{
                                    'title': response.title,
                                    'start': response.start_date,
                                    'end': response.end_date,
                                    'color': response.color,
                                })
                            },
                            error: function(error) {
                                if (error.responseJSON.errors) {
                                    $('#titleError').html(error.responseJSON.errors.title);
                                }
                            },
                        });
                    });
                },
                //Drop bookings
                editable:true,
                eventDrop: function(event) {
                    if (!event.id) {
                        console.error("Erreur: ID d'événement manquant");
                        return;
                    }
                    var id = event.id;
                    var start_date = moment(event.start).format('YYYY-MM-DD HH:mm');
                    var end_date = moment(event.end).format('YYYY-MM-DD HH:mm');

                    $.ajax({
                        url: "{{ route('calendar.update', '') }}" + '/' + id,
                        type: "PATCH", // HTTP verb for updates
                        dataType: 'json',
                        data: { start_date, end_date },
                        success: function(response) {
                            swal("Opération réussie!", "Book is updated succefully!", "success");
                        },
                        error: function(error) {
                            console.error('Error:', error.responseText || error);
                        },
                    });
                },
                // Suppression d'un événement existant
                eventClick: function (event) {
                    var id = event.id;

                    if (!id) {
                        console.error("Erreur : ID de l'événement manquant.");
                        return;
                    }

                    if (confirm('Êtes-vous sûr de vouloir supprimer ce booking ?')) {
                        // Requête AJAX pour supprimer l'événement
                        $.ajax({
                            url: "{{ route('calendar.destroy', '') }}" + '/' + id,
                            type: "DELETE",
                            dataType: 'json',
                            success: function (response) {
                                $('#calendar').fullCalendar('removeEvents', id);
                            },
                            error: function (error) {
                                console.error('Error:', error.responseText || error);
                            },
                        });
                    }
                },
                //Disabled multiple select book creation
                selectAllow: function(selectInfo) {
                    return moment(selectInfo.start)
                        .utcOffset(false)
                        .isSame(moment(selectInfo.end).subtract(1, 'second').utcOffset(false), 'day');
                }
            });
            // Réinitialiser les événements du modal lorsque celui-ci est fermé
            $("#bookingModal").on("hidden.bs.modal", function () {
                $('#titleError').html('');
                $('#title').val('');
                $('#addBooking').unbind();
            });
            /*$('.fc-event').css('font-size','16px');
            $('.fc-event').css('width','20px');
            $('.fc-event').css('border-radius','50%'); */

            $('.fc').css('background','skyblue');
            $('.fc').css('color','#000');
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>