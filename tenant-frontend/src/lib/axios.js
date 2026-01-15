import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost/api',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
  }
})

export default api
