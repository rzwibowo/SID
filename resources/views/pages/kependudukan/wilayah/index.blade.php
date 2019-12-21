@extends('layouts.default')
    @section('content')
    <div class="content">
        <div class="page-inner">
            <div class="page-header">
                <h4 class="page-title">Wilayah</h4>

            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title">Data Wilayah</h4>
                                <a class="btn btn-primary btn-round ml-auto" href="wilayah/add">
                                <i class="fa fa-plus"></i> Tambah Wilayah
                                </a>
                            </div>
                        </div>
                        <div class="card-body">

                            <div class="table-responsive">
                                <div id="add-row_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6">
                                            <div class="dataTables_length" id="add-row_length">
                                                <label>Show
                                                    <select name="show_data" id="show_data" onchange="searchChange()" aria-controls="add-row" class="form-control form-control-sm">
                                                        <option value="10">10</option>
                                                        <option value="25">25</option>
                                                        <option value="50">50</option>
                                                        <option value="100">100</option>
                                                    </select> entries</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div id="add-row_filter" class="dataTables_filter">
                                                <label>Search:
                                                    <input type="search" name="search" id="search" onkeyup="searchEnter(event)" class="form-control form-control-sm" placeholder="" aria-controls="add-row">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <table id="add-row" class="display table table-striped table-hover dataTable" role="grid" aria-describedby="add-row_info">
                                                <thead>
                                                    <tr role="row">
                                                    <th  tabindex="0" aria-controls="add-row" rowspan="1" colspan="1">No</th>
                                                        <th class="sorting_asc" tabindex="0" aria-controls="add-row" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Name: activate to sort column descending" style="width: 233px;">Dusun</th>
                                                        <th class="sorting" tabindex="0" aria-controls="add-row" rowspan="1" colspan="1" aria-label="Position: activate to sort column ascending" style="width: 344px;">Kepala Dusun</th>
                                                        <th style="width: 108px;" class="sorting" tabindex="0" aria-controls="add-row" rowspan="1" colspan="1" aria-label="Action: activate to sort column ascending">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php $no =1 ?>
                                                @foreach ($wilayah as $item)
                                                    <tr role="row" class="{{$no%2?'odd':'even'}}">
                                                        <td class="sorting_1">{{$no++}}</td>
                                                        <td class="sorting_1">{{$item->wilayah_nama}}</td>
                                                        <td>{{$item->full_name}}</td>
                                                        <td>
                                                            <div class="form-button-action">
                                                                <a href="{{url('kependudukan/wilayah/edit/'.$item->wilayah_id)}}" class="btn btn-link btn-primary btn-lg" title="Edit">
                                                                    <i class="fa fa-edit"></i>
                                                                </a>
                                                                <a href="{{url('kependudukan/wilayah/view/'.$item->wilayah_id)}}" class="btn btn-link btn-primary btn-lg" title="Show">
                                                                    <i class="fa fa-eye"></i>
                                                                </a>
                                                                <a title="Delete" class="btn btn-link btn-danger"  onclick="return confirm('Anda akan menghapus?')" href="{{url('kependudukan/wilayah/delete/'.$item->wilayah_id)}}">
                                                                    <i class="fa fa-times"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-5">
                                            <div class="dataTables_info" id="add-row_info" role="status" aria-live="polite">Showing 1 to 5 of 10 entries
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-7">
                                            <div class="dataTables_paginate paging_simple_numbers" id="add-row_paginate">
                                                <ul class="pagination">
                                                    <li class="paginate_button page-item previous disabled" id="add-row_previous">
                                                    <a onClick="" aria-controls="add-row" data-dt-idx="0" tabindex="0" class="page-link" onmouseover="searchPage(this,0)">Previous</a></li>
                                                    <li class="paginate_button page-item active"><a href="#" onmouseover="searchPage(this,1)" aria-controls="add-row" data-dt-idx="1" tabindex="0" class="page-link" onclick="filter_data()">1</a></li>
                                                    <li class="paginate_button page-item "><a href="#" onmouseover="searchPage(this,2)" aria-controls="add-row" data-dt-idx="2" tabindex="0" class="page-link" onclick="filter_data()">2</a></li>
                                                    <li class="paginate_button page-item next" id="add-row_next"><a href="#" onmouseover="searchPage(this,3)" aria-controls="add-row" data-dt-idx="3" tabindex="0" class="page-link">Next</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var currentUrl = new URL(window.location.href);
        var search_val = currentUrl.searchParams.get("search");
        $("#search").val(search_val);

        var baseUrl = "{{url('')}}" + window.location.pathname;

        function filter_data(page=null){     
            var showdata = $("#show_data").val();
            var search =  $("#search").val();
            
            return baseUrl+"?search="+search+"&showdata="+showdata+"&page="+page;
        }

        function searchEnter(event){
            if (event.keyCode === 13) {
                window.location.href = filter_data();
            }
        }

        function searchChange(){
                window.location.href = filter_data();
        }
        
        function searchPage(event,page)
        {
            event.href = filter_data(page);
        }
        
    </script>   
    @stop