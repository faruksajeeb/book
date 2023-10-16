<template>
  <div
    class="modal fade"
    id="recordModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="recordModalLabel"
  >
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title" id="recordModalLabel">Book Details</h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Display record details here -->
         
          <div v-if="record.book">
            <div class="row">             
              <div class="col-md-8">
                <table class="table table-sm">
                  <tr>
                    <td>Damage Item</td>
                    <td>{{ record.book.title }}</td>
                  </tr>
                  <tr>
                    <td>Damage Date</td>
                    <td>{{ record.damage_date }}</td>
                  </tr>
                  <tr>
                    <td>Damage Quantity</td>
                    <td>{{ record.quantity }}</td>
                  </tr>
                  <tr>
                    <td>Created At</td>
                    <td>{{ record.created_at }}</td>
                  </tr>
                  <tr>
                    <td>Updated At</td>
                    <td>{{ record.updated_at }}</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>

          <div v-else>
            <LoadingSpinner/>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
    data(){
        return{            
            
        }
    },
    props: {
        record:{},
    },
    created(){
       
    },
}
</script>
