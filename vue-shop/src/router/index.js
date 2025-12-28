import { createRouter, createWebHistory } from "vue-router";
import Products from "../views/Products.vue";
import Cart from "../views/Cart.vue";
import Login from "../views/Login.vue";
import Register from "../views/Register.vue";
import Order from "../views/order.vue";
import AdminDashboard from "../views/admindashboard.vue";
import SellerRequests from "../views/SellerRequests.vue";


const routes = [
  { path: "/", component: Products },
  { path: "/login", component: Login },
  { path: "/register", component: Register },
  {
    path: "/cart",
    component: Cart,
    meta: { requiresAuth: true }
  },
  {
    path: "/orders",
    component: Order,
    meta: { requiresAuth: true }
  },
  {
    path: "/admin/dashboard",
    component: AdminDashboard,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: "/admin/seler_requests",
    component: SellerRequests,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
  path: '/admin/categories',
  component: () => import('../views/admincategories.vue'),
  meta: { requiresAuth: true, requiresAdmin: true }
}
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to, from, next) => {
  const isAuth = !!localStorage.getItem("auth_token");
  const userStr = localStorage.getItem("user");
  let isAdmin = false;
  
  try {
    const user = userStr ? JSON.parse(userStr) : null;
    if (user) {
      if (user.role === 'admin') isAdmin = true;
      if (typeof user.hasRole === 'function' && user.hasRole('admin')) isAdmin = true;
      if (Array.isArray(user.roles)) {
        isAdmin = user.roles.some(r => r && (r.name === 'admin' || r === 'admin'));
      }
    }
  } catch (e) {}

  if (to.meta.requiresAuth && !isAuth) {
    next("/login");
  } else if (to.meta.requiresAdmin && !isAdmin) {
    next("/");
  } else if (isAuth && isAdmin && to.path === "/") {
    next("/admin/dashboard");
  } else if (isAuth && isAdmin && to.path === "/login") {
    next("/admin/dashboard");
  } else {
    next();
  }
});

export default router;
