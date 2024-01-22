<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        @page {
            margin-left: 3%;
            margin-right: 2%;
            margin-top: 5px;
            margin-bottom: 0px;
        }

        .page-break {
            page-break-after: always;
        }

        .barcode {
            width: 100%;

            text-align: center;
        }

        .created_at {
            margin: 2%;
            width: 95%;
            text-align: right;
            font-size: 12px;
            font-style: italic;
        }

        .articles {
            text-align: center;
            width: 100%;
            font-style: italic;
            font-size: 16px;
            text-decoration: underline;
        }

        .title {
            top: 2%;
            text-align: center;
            text-decoration: dashed;
            font-weight: bold;
            font-size: 16px;
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
    
  
   @foreach($data as $d)
   <div class="title">{{$d['organization']}}</div>
    <div class="created_at">
        <div><span>Дата: </span>{{$d['crated_at']}}</div>
        <div><span>Сформировал: </span>{{$d['user']}}</div>
    </div>
    <div class="articles">
        {{$d['article']}}
    </div>
    <div class="codegred">
        <div class="barcode"><span>{!! DNS1D::getBarcodeHTML($d['box_name'],'C128',2.6,80) !!}</span>
            <div class="title2">{{$d['box_name']}}</div>
        </div>
    </div>

    @if(count($data)>$loop->iteration)
    <div class="page-break"></div>
    @endif
    @endforeach
</div>

</html>