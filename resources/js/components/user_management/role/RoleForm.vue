<template>
  <div>
    <div class="row">
      <div class="col-md-12">
        <div class="card shadow-sm my-4">
          <div class="card-header py-1 my-bg-success">
            <!-- <h3 class="text-white-900" v-if="isNew">
              <i class="fa fa-plus"></i> Add Role
            </h3>
            <h3 class="text-white-900" v-else><i class="fa fa-pencil"></i> Edit Role</h3> -->
          </div>
          <div class="card-body p-3">
            <div class="form">
              <form
                class="user"
                @submit.prevent="submitForm"
                @keydown="form.onKeydown($event)"
              >
                <AlertError :form="form" />
                <div class="form-group">
                  <div class="form-row">
                    <div class="col-md-12">
                      <label for="">Role Name <span class="text-danger">*</span></label>
                      <input
                        type="text"
                        class="form-control"
                        id="exampleInputFirstName"
                        placeholder="Enter Your role Name"
                        v-model="form.name"
                        :class="{ 'is-invalid': form.errors.has('name') }"
                      />
                      <HasError :form="form" field="name" />
                    </div>
                  </div>
                  <div class="form-group mt-3">
                    <!-- List of available permissions -->
                    <div class="available-permissions">
                      <h5>Available Permissions <span class="text-danger">*</span></h5>
                      <HasError :form="form" field="selectedPermissions" />
                      <div class="row">
                        <!-- <div v-if="permissions.length > 0"> -->
                        <div
                          class="col-md-3 border-top py-2"
                          v-for="(group, groupName) in permissions"
                          :key="groupName"
                        >
                          <h5>{{ groupName.replace("_", " ").toUpperCase() }}</h5>
                          <ul>
                            <li v-for="(permission, index) in group" :key="permission.id">
                              <input
                                type="checkbox"
                                :id="permission.id"
                                v-model="form.selectedPermissions"
                                :value="permission.id"
                              />
                              <label :for="permission.id" class="mx-2">{{
                                permission.name
                              }}</label>
                            </li>
                          </ul>
                        </div>
                        <!-- </div> -->
                      </div>

                      <!-- <div v-else>
                        <LoadingSpinner />
                      </div> -->
                    </div>
                  </div>
                </div>
                <hr />
                <div class="form-group">
                  <router-link to="/roles"> Manage Role </router-link>
                  <save-button :is-submitting="isSubmitting"></save-button>
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
// import {Form} from "vform";
export default {
  data: () => ({
    isSubmitting: false,
    role: null,
    permissions: {},
    form: new Form({
      name: "",
      selectedPermissions: [], // Store selected permissions
    }),
  }),
  computed: {
    isNew() {
      return !this.$route.path.includes("edit");
    },
  },
  async created() {
    if (!User.loggedIn()) {
      this.$router.push("/");
    }
    try {
      this.permissions = this.$store.getters.getPemissions;
      console.log(this.permissions);
      if (this.permissions.length == 0) {
        const response = await axios.get("/api/get-permissions");
        this.permissions = response.data;
        console.log(this.permissions);
      }
    } catch (error) {
      console.error("Error fetching roles:", error);
    }
    if (!User.loggedIn()) {
      // this.$router.push({ name: "/" });
    }
    if (!this.isNew) {
      const response = await axios.get(`/api/roles/${this.$route.params.id}`);
      // alert(response.data);
      this.form.name = response.data.name;
      // console.log(response.data.permissions);
      this.form.selectedPermissions = response.data.permissions.map(
        (permission) => permission.id
      );
    }
  },
  methods: {
    async submitForm() {
      this.isSubmitting = true;
      if (this.isNew) {
        await this.form
          .post("/api/roles", this.form)
          .then(() => {
            this.$router.push({ name: "roles" });
            Notification.success(
              `Create role & Assigned permissions to role ${this.form.name}`
            );
          })
          .catch((error) => {
            // console.log(error);
            if (error.response.status === 422) {
              this.errors = error.response.data.errors;
              Notification.error("Validation Errors!");
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
      } else {
        try {
          console.log(this.form);
          await this.form
            .put(`/api/roles/${this.$route.params.id}`, this.form)
            .then((response) => {
              Notification.success("Role info Updated");
              this.$router.push("/roles");
            })
            .catch((error) => {
              Notification.error(error);
            })
            .finally(() => {
              // always executed;
              this.isSubmitting = false;
            });
        } catch (error) {
          Notification.error(error);
        }
      }
    },
  },
};
</script>

<style type="text/css" scoped>
ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
}
</style>
