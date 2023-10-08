<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Excel Downloader</title>
    <!-- Jquery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <div class="loading-overlay">
        <div class="loading-container">
            <!-- Loading effect -->

            <div class="loading-text">
                <p>Generating Excel file...This may take about <span id="minutes">10</span> minutes.</p>
            </div>
            <div class="loading-spinner"></div>
        </div>
    </div>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>Excel Downloader for employees of HRBrain</h3>
            </div>
            <div class="card-body">
                <h5> * Select year</h5>
                <select name="year" id="year">
                    <option value="2020">2020</option>
                    <option value="2021">2021</option>
                    <option value="2022">2022</option>
                </select>

                <button class="btn btn-primary" id="download">Download</button>
            </div>
            {{-- <div class="row mt-5">
                <div class="col-auto">
                    <select class="form-select" aria-label="Default select example">
                        <option selected>Choose your years</option>
                        <option value="1">2020</option>
                        <option value="2">2021</option>
                        <option value="3">2022</option>
                    </select>
                </div>

                <div class="col-auto">
                    <a href="{{route('export')}}">
                        <button type="submit" class="btn btn-primary mb-3">Download Excel</button>
                    </a>
                </div>
            </div> --}}



        </div>
    </div>

</body>
<script>
    $(document).ready(function() {

        $("#download").click(function() {
            $(".loading-overlay").css('display', 'block');
            var selectedYear = $("#year").val();
            console.log(selectedYear);
            if (selectedYear == "2022") {
                $('#minutes').text('15');
            }
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('download#excel') }}",
                type: "POST",
                data: {
                    year: selectedYear
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data) {
                    console.log(data);
                    // var a = document.createElement('a');
                    // var url = window.URL.createObjectURL(data);
                    // a.href = url;
                    // a.download = `employees_${selectedYear}.xlsx`;
                    // document.body.append(a);
                    // a.click();
                    // window.URL.revokeObjectURL(url);
                    $(".loading-overlay").css('display', 'none');
                },
                error: function() {
                    $(".loading-overlay").css('display', 'none');
                }
            })

        })
    })
</script>

</html>
