<template>
  <div>
    <div class="row">
      <div class="col-md-6 offset-md-3">
        <div class="card shadow-sm my-4">
          <div class="card-header py-2 my-bg-success">
            <h3 class="text-light-900"><i class="fa fa-plus"></i> Edit Permission</h3>
          </div>
          <div class="card-body p-3">
            <div class="form">
              <form class="user" @submit.prevent="permissionUpdate">
                <div class="form-group">
                  <label for="">Permission Group</label>
                  <select
                    v-model="form.group_name"
                    @change="getPermissionGroup"
                    @blur="handleBlur"
                    class="form-select"
                    :class="{ 'is-invalid': errors.group_name }"
                  >
                    <option value="">--select group--</option>
                    <option value="user">User</option>
                    <option value="role">Role</option>
                    <option value="permission">Permission</option>
                    <option value="category">Category</option>
                    <option value="sub_category">Sub Category</option>
                    <option value="option_group">Option Group</option>
                    <option value="option">Option</option>
                    <option value="author">Author</option>
                    <option value="publisher">Publisher</option>
                    <option value="customer">Customer</option>
                    <option value="supplier">Supplier</option>
                    <option value="book">Book</option>
                    <option value="sale">Sale</option>
                    <option value="purchase">Purchase</option>
                    <option value="sale_return">Sale Return</option>
                    <option value="purchase_return">Purchase Return</option>
                    <option value="customer_payment">Customer Payment</option>
                    <option value="supplier_payment">Supplier Payment</option>
                    <option value="damage_item">Damage Item</option>
                    <option value="report">Report</option>
                    <option value="setting">Setting</option>
                  </select>
                  <small class="text-danger" v-if="errors.group_name">
                    {{ errors.group_name[0] }}
                  </small>
                </div>
                <div class="form-group">
                  <label for="">permission Name:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="exampleInputFirstName"
                    placeholder="Enter Your permission Name"
                    v-model="form.name"
                    :class="{ 'is-invalid': errors.name }"
                  />
                  <small class="text-danger" v-if="errors.name">
                    {{ errors.name[0] }}
                  </small>
                </div>
                <hr />
                <div class="form-group">
                  <router-link to="/manage-permission"> Manage Permission </router-link>
                  <save-changes-button
                    :is-submitting="isSubmitting"
                  ></save-changes-button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script type="text/javascript">
export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push("/");
    }
  },

  data() {
    return {
      isSubmitting: false,
      form: {
        name: "",
        group_name: "",
      },
      errors: {},
    };
  },
  created() {
    let id = this.$route.params.id;

    axios
      .get("/api/manage-permission/" + id)
      .then(({ data }) => (this.form = data))
      .catch((error) => {
        console.log(error);
        if (error.response.status === 422) {
          this.errors = error.response.data.errors;
          Notification.error(error.response.statusText);
        } else if (error.response.status === 401) {
          // statusText = "Unauthorized";
          this.errors = {};
          Notification.error(error.response.data.error);
        } else {
          Notification.error(error.response.statusText);
        }
      });
  },

  methods: {
    permissionUpdate() {
      this.isSubmitting = true;
      let id = this.$route.params.id;
      axios
        .patch("/api/manage-permission/" + id, this.form)
        .then(() => {
          this.$router.push({ name: "manage-permission" });
          Notification.success("Data Updated Successfully!");
        })
        .catch((error) => {
          console.log(error);
          if (error.response.status === 422) {
            this.errors = error.response.data.errors;
            Notification.error(error.response.statusText);
          } else if (error.response.status === 401) {
            // statusText = "Unauthorized";
            this.errors = {};
            Notification.error(error.response.data.error);
          } else {
            Notification.error(error.response.statusText);
          }
        })
        .finally(() => {
          // always executed;
          this.isSubmitting = false;
        });
    },
    getPermissionGroup() {
      const permissionGroup = this.form.group_name;
      this.form.name = permissionGroup + ".";
      this.$refs.myInput.focus();
      this.form.clear();
    },
  },
};
</script>

<style type="text/css"></style>
