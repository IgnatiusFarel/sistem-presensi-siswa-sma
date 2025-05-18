import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api', 
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },  
  timeout: 15000,
})

// Request interceptor
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('auth_token');
    const tokenExpiry = localStorage.getItem('token_expiry');
    const isExpired = tokenExpiry && new Date() > new Date(tokenExpiry);

    // Only add token if it exists and isn't expired
    if (token && !isExpired) {
      config.headers.Authorization = `Bearer ${token}`
    } else if (token && isExpired) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user'); 
      localStorage.removeItem('token_expiry'); 

      window.location.href = '/masuk?expired=true'; 
      return Promise.reject(new Error('Token expired'));
    }

    return config; 
  },
  error => Promise.reject(error)
);

// Response interceptor - IMPORTANT FIX HERE
api.interceptors.response.use(
  response => response,
  error => {
    // Only redirect for token issues on protected routes, NOT login failures
    if (error.response?.status === 401 && 
        !error.config.url.includes('/masuk')) {
      // This is a token issue on a protected route
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      localStorage.removeItem('token_expiry');

      window.location.href = '/masuk?expired=true'
    }
    return Promise.reject(error)
  }
)

export default api
