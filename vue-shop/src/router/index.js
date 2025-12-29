import { createRouter, createWebHistory } from "vue-router";
import Products from "../views/Products.vue";
import Cart from "../views/Cart.vue";
import Login from "../views/Login.vue";
import Register from "../views/Register.vue";
import Order from "../views/order.vue";
import AdminDashboard from "../views/admindashboard.vue";
import SellerRequests from "../views/SellerRequests.vue";
import SellerDashboard from "../views/sellerdashboard.vue";
import SellerProducts from "../views/SellerProducts.vue";



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
},
{
  path: "/seller/dashboard",
  component: SellerDashboard,
  meta: { requiresAuth: true, requiresSeller: true }
},
{
  path: "/seller/products",
  component: SellerProducts,
  meta: { requiresAuth: true, requiresSeller: true }
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
  let isSeller = false;

  try {
    const user = userStr ? JSON.parse(userStr) : null;

    if (user && Array.isArray(user.roles)) {
      isAdmin  = user.roles.some(r => r?.name === "admin"  || r === "admin");
      isSeller = user.roles.some(r => r?.name === "seller" || r === "seller");
    }
  } catch (e) {}

  // 🔐 Auth check
  if (to.meta.requiresAuth && !isAuth) {
    return next("/login");
  }

  // 👑 Admin guard
  if (to.meta.requiresAdmin && !isAdmin) {
    return next("/");
  }

  // 🛒 Seller guard
  if (to.meta.requiresSeller && !isSeller) {
    return next("/");
  }

  // Redirects
  if (isAuth && isAdmin && to.path === "/") {
    return next("/admin/dashboard");
  }

  if (isAuth && isSeller && to.path === "/") {
    return next("/seller/dashboard");
  }

  if (isAuth && to.path === "/login") {
    if (isAdmin) return next("/admin/dashboard");
    if (isSeller) return next("/seller/dashboard");
    return next("/");
  }

  next();
});


export default router;
