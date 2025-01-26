<template lang="">
    <div id="dashboard">
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
              <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
              </ol>
            </div>

            <div class="row mb-3">
              <!-- Earnings (Monthly) Card Example -->
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100">
                  <div class="card-body p-4">
                    <div class="row align-items-center">
                      <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Today Sale Amount</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">৳ {{ todaySale }}</div>
                        <div class="mt-2 mb-0 text-muted text-xs">
                          <!-- <span class="text-success mr-2"><i class="fa fa-arrow-up"></i> 3.48%</span>
                          <span>Since last month</span> -->
                        </div>
                      </div>
                      <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-primary"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Earnings (Annual) Card Example -->
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100">
                  <div class="card-body p-4">
                    <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Today Purchase Amount</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">৳ {{ todayPurchase }} </div>
                        <!-- <div class="mt-2 mb-0 text-muted text-xs">
                          <span class="text-success mr-2"><i class="fas fa-arrow-up"></i> 12%</span>
                          <span>Since last years</span>
                        </div> -->
                      </div>
                      <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-success"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- New User Card Example -->
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100">
                  <div class="card-body p-4">
                    <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Due</div>
                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">৳ {{ totalDue }} </div>
                        <!-- <div class="mt-2 mb-0 text-muted text-xs">
                          <span class="text-success mr-2"><i class="fas fa-arrow-up"></i> 20.4%</span>
                          <span>Since last month</span>
                        </div> -->
                      </div>
                      <div class="col-auto">
                        <i class="fas fa-2x text-info">৳</i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Pending Requests Card Example -->
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100">
                  <div class="card-body p-4">
                    <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Customers</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"> {{ totalCustomer }} </div>
                        <!-- <div class="mt-2 mb-0 text-muted text-xs">
                          <span class="text-danger mr-2"><i class="fas fa-arrow-down"></i> 1.10%</span>
                          <span>Since yesterday</span>
                        </div> -->
                      </div>
                      <div class="col-auto">
                        <i class="fas fa-users fa-2x text-warning"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Area Chart -->
              <!-- <div class="col-xl-8 col-lg-7">
                <div class="card mb-4">
                  <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Recap Report</h6>
                    <div class="dropdown no-arrow">
                      <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                      </a>
                      <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Dropdown Header:</div>
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Something else here</a>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="chart-area">
                      <canvas id="myAreaChart"></canvas>
                    </div>
                  </div>
                </div>
              </div> -->
              <!-- Pie Chart -->
              <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card">
                  <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Out of stock</h6>
                    <!-- <a class="m-0 float-right btn btn-danger btn-sm" href="#">View More <i
                        class="fas fa-chevron-right"></i></a> -->
                  </div>
                  <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                      <thead class="thead-light">
                        <tr>
                        <th>Name</th>
                        <th>Photo</th>                         
                        <th>Price</th>
                        <th>Status</th>
                        <th>Quantity</th>                        
                      </tr>
                      </thead>
                      <tbody v-if="out_of_stocks.length>0">
                        <tr v-for="product in out_of_stocks" :key="product.id">
                        <td> {{ product.title }} </td>
                        <td><img
                        :src="
                          `${publicPath}assets/img/book/thumbnail/` +
                          product.photo
                        "
                        alt=""
                        width="30"
                      /></td>
                        
                        <td>{{ product.price }}</td>
                          <td v-if="product.stock_quantity  >= 1 "><span class="badge badge-success">Available </span></td>
                          <td v-else=" "><span class="badge badge-danger">Stock Out </span></td>
                         <td>{{ product.stock_quantity }}</td>
             
                      </tr>
                      </tbody>
                      <tbody v-else>
                      <tr>
                        <td colspan="5">
                          <LoadingSpinner/>
                        </td>
                      </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="card-footer"></div>
                </div>
              </div>
              <div class="col-xl-4 col-lg-5">
                <div class="card mb-4">
                  <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Stock Alert</h6>
                    <div class="dropdown no-arrow">
                    <i class="fas fa-bell text-danger"></i>
                      <!-- <a class="dropdown-toggle btn btn-primary btn-sm" href="#" role="button" id="dropdownMenuLink"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Month <i class="fas fa-chevron-down"></i>
                      </a> -->
                      <!-- <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Select Periode</div>
                        <a class="dropdown-item" href="#">Today</a>
                        <a class="dropdown-item" href="#">Week</a>
                        <a class="dropdown-item active" href="#">Month</a>
                        <a class="dropdown-item" href="#">This Year</a>
                      </div> -->
                    </div>
                  </div>
                  <div class="card-body">
                    <!-- <div class="mb-3">
                      <div class="small text-gray-500">Oblong T-Shirt
                        <div class="small float-right"><b>600 of 800 Items</b></div>
                      </div>
                      <div class="progress" style="height: 12px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 80%" aria-valuenow="80"
                          aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </div> -->
                    <table class="table ">
                      <thead>
                        <th>Name</th>
                        <th>Qty.</th>
                      </thead>
                      <tbody v-if="!isLoading && stock_alerts.length > 0">
                        <tr v-for="item in stock_alerts" :key="item.id">
                          <td>{{ item.title }}</td>
                          <td class="text-center">{{ item.stock_quantity }}</td>
                        </tr>
                      </tbody>
                      <tbody v-else-if="isLoading">
                        <tr>
                          <td colspan="2">
                            <LoadingSpinner />
                          </td>
                        </tr>
                      </tbody>
                      <tbody v-else>
                        <tr>
                          <td colspan="2" class="text-center">No stock alerts found.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <!-- <div class="card-footer text-center">
                    <a class="m-0 small text-primary card-link" href="#">View More <i
                        class="fas fa-chevron-right"></i></a>
                  </div> -->
                </div>
              </div>
              <!-- Invoice Example -->
              <!-- <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card">
                  <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Invoice</h6>
                    <a class="m-0 float-right btn btn-danger btn-sm" href="#">View More <i
                        class="fas fa-chevron-right"></i></a>
                  </div>
                  <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                      <thead class="thead-light">
                        <tr>
                          <th>Order ID</th>
                          <th>Customer</th>
                          <th>Item</th>
                          <th>Status</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td><a href="#">RA0449</a></td>
                          <td>Udin Wayang</td>
                          <td>Nasi Padang</td>
                          <td><span class="badge badge-success">Delivered</span></td>
                          <td><a href="#" class="btn btn-sm btn-primary">Detail</a></td>
                        </tr>
                        <tr>
                          <td><a href="#">RA5324</a></td>
                          <td>Jaenab Bajigur</td>
                          <td>Gundam 90' Edition</td>
                          <td><span class="badge badge-warning">Shipping</span></td>
                          <td><a href="#" class="btn btn-sm btn-primary">Detail</a></td>
                        </tr>
                        <tr>
                          <td><a href="#">RA8568</a></td>
                          <td>Rivat Mahesa</td>
                          <td>Oblong T-Shirt</td>
                          <td><span class="badge badge-danger">Pending</span></td>
                          <td><a href="#" class="btn btn-sm btn-primary">Detail</a></td>
                        </tr>
                        <tr>
                          <td><a href="#">RA1453</a></td>
                          <td>Indri Junanda</td>
                          <td>Hat Rounded</td>
                          <td><span class="badge badge-info">Processing</span></td>
                          <td><a href="#" class="btn btn-sm btn-primary">Detail</a></td>
                        </tr>
                        <tr>
                          <td><a href="#">RA1998</a></td>
                          <td>Udin Cilok</td>
                          <td>Baby Powder</td>
                          <td><span class="badge badge-success">Delivered</span></td>
                          <td><a href="#" class="btn btn-sm btn-primary">Detail</a></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="card-footer"></div>
                </div>
              </div> -->
              <!-- Message From Customer-->
              <!-- <div class="col-xl-4 col-lg-5 ">
                <div class="card">
                  <div class="card-header py-4 bg-primary d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-light">Message From Customer</h6>
                  </div>
                  <div>
                    <div class="customer-message align-items-center">
                      <a class="font-weight-bold" href="#">
                        <div class="text-truncate message-title">Hi there! I am wondering if you can help me with a
                          problem I've been having.</div>
                        <div class="small text-gray-500 message-time font-weight-bold">Udin Cilok · 58m</div>
                      </a>
                    </div>
                    <div class="customer-message align-items-center">
                      <a href="#">
                        <div class="text-truncate message-title">But I must explain to you how all this mistaken idea
                        </div>
                        <div class="small text-gray-500 message-time">Nana Haminah · 58m</div>
                      </a>
                    </div>
                    <div class="customer-message align-items-center">
                      <a class="font-weight-bold" href="#">
                        <div class="text-truncate message-title">Lorem ipsum dolor sit amet, consectetur adipiscing elit
                        </div>
                        <div class="small text-gray-500 message-time font-weight-bold">Jajang Cincau · 25m</div>
                      </a>
                    </div>
                    <div class="customer-message align-items-center">
                      <a class="font-weight-bold" href="#">
                        <div class="text-truncate message-title">At vero eos et accusamus et iusto odio dignissimos
                          ducimus qui blanditiis
                        </div>
                        <div class="small text-gray-500 message-time font-weight-bold">Udin Wayang · 54m</div>
                      </a>
                    </div>
                    <div class="card-footer text-center">
                      <a class="m-0 small text-primary card-link" href="#">View More <i
                          class="fas fa-chevron-right"></i></a>
                    </div>
                  </div>
                </div>
              </div> -->
            </div>
    
    </div>
</template>
<script>

export default {  
  
  data(){
    return{
      publicPath: window.publicPath,
      todaySale:0,
      todayPurchase:0,
      totalDue:0,
      totalCustomer:0,
      out_of_stocks:[],
      stock_alerts:[],
      isLoading: true, 
    }
  },
  created() {
    this.TodaySale();
    this.TodayPurchase();
    this.TotalDue();
    this.TotalCustomer();
    this.OutOfStock();
    this.StockAlerts();
  },
  mounted(){    
    
 },
  methods:{
    async TodaySale(){
      await axios.get('/api/report/today-sale')
        .then(({data}) => (this.todaySale = data))
        .catch()
     },
     async TodayPurchase(){
      await axios.get('/api/report/today-purchase')
        .then(({data}) => (this.todayPurchase = data))
        .catch()
     },
     async TotalDue(){
      await axios.get('/api/report/total-due')
        .then(({data}) => (this.totalDue = data))
        .catch()
     },
     async TotalCustomer(){
      await axios.get('/api/report/total-customer')
        .then(({data}) => (this.totalCustomer = data))
        .catch()
     },

     async OutOfStock(){
      await axios.get('/api/report/out-of-stock')
        .then(({data}) => (this.out_of_stocks = data))
        .catch()
     },
     async StockAlerts(){
      this.isLoading = true; 
      await axios
        .get('/api/report/stock-alerts')
        .then(({ data }) => {
          this.stock_alerts = data; // Update stock alerts
        })
        .catch((error) => {
          console.error('Error fetching stock alerts:', error); // Handle any errors
        })
        .finally(() => {
          this.isLoading = false; // Set loading state to false after API call completes
        });
     },

   }
}
</script>
<style lang="">
    
</style>