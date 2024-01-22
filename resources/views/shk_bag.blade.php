<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        @page {
            margin-left: 2%;
            margin-right: 2%;
            margin-top: 2px;
            margin-bottom: 2px;
        }

        .page-break {
            page-break-after: always;
        }

        .barcode {
            width: 100%;

            text-align: center;
        }


        .title2 {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }


        .codegred {
            left: auto;
            position: absolute;
            text-align: center;
            align-content: center;
            left: 10%;
            bottom: 2%;
            margin: auto 0;
        }

        * {
            font-family: DejaVu Sans mono !important;
        }
    </style>
</head>
<div>
    
   @foreach($bag as $d)
   
    <div class="codegred">
        <div class="barcode"><span>{!! DNS1D::getBarcodeHTML($d['name'],'C128',1.3,80) !!}</span>
            <div class="title2">{{$d['name']}}</div>
        </div>
    </div>
    @if(count($bag)>$loop->iteration)
    <div class="page-break"></div>
    @endif
    @endforeach
</div>

</html>