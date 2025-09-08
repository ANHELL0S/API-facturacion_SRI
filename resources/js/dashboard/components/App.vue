<template>
  <div>
    <div v-if="!token">
      <Login v-if="currentAuthView === 'login'" @login-success="handleLoginSuccess" @show-register="currentAuthView = 'register'" />
      <Register v-else @register-success="currentAuthView = 'login'" @show-login="currentAuthView = 'login'" />
    </div>
    <div v-else>
      <div v-if="isLoading" class="flex items-center justify-center min-h-screen">
        <p class="text-xl">Cargando...</p>
      </div>
      <div v-else>
        <AdminDashboard v-if="userRole === 'admin'" :key="userId" :token="token" @logout="handleLogout" />
        <Dashboard v-else :key="userId" :token="token" @logout="handleLogout" />
      </div>
    </div>
  </div>
</template>

<script>
import Login from './Login.vue';
import Register from './Register.vue';
import Dashboard from './Dashboard.vue';
import AdminDashboard from './AdminDashboard.vue';
import axios from 'axios';
import downloadStore from '../utils/downloadStore.js';

export default {
  name: 'App',
  components: {
    Login,
    Register,
    Dashboard,
    AdminDashboard,
  },
  data() {
    return {
      token: localStorage.getItem('jwt_token') || null,
      userRole: null,
      userId: null,
      isLoading: true,
      currentAuthView: 'login',
    };
  },
  created() {
    if (this.token) {
      downloadStore.setToken(this.token);
      downloadStore.setEmitter(this.$emitter);
      this.fetchUserProfile();
    } else {
      this.isLoading = false;
    }
  },
  methods: {
    async handleLoginSuccess(token) {
      this.isLoading = true;
      localStorage.setItem('jwt_token', token);
      this.token = token;
      downloadStore.setToken(token);
      downloadStore.setEmitter(this.$emitter);
      await this.fetchUserProfile();
    },
    async fetchUserProfile() {
      try {
        const response = await axios.get('/api/me', {
          headers: { Authorization: `Bearer ${this.token}` },
        });
        const user = response.data.data;

        this.userId = user.id;
        if (user.roles && user.roles.some(role => role.name === 'admin')) {
          this.userRole = 'admin';
        } else {
          this.userRole = 'client';
        }
      } catch (error) {
        console.error('Error fetching user profile:', error);
        this.handleLogout();
      } finally {
        this.isLoading = false;
      }
    },
    async handleLogout() {
      try {
        if (this.token) {
            await axios.post('/api/logout', {}, {
                headers: { 'Authorization': `Bearer ${this.token}` }
            });
        }
      } catch (error) {
        console.error('Error during logout:', error);
      } finally {
        localStorage.removeItem('jwt_token');
        localStorage.removeItem('correctiveBillingData');
        this.token = null;
        this.userRole = null;
        this.userId = null;
      }
    },
  },
};
</script>
