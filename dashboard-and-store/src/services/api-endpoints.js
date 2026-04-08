export const api_users = {
  list: '/users',                // GET|HEAD
  create     : '/users',                         // POST
  single     : (id) => `/users/${id}`,           // GET|HEAD
  update     : (id) => `/users/${id}`,           // PUT|PATCH
  delete     : (id) => `/users/${id}`,           // DELETE (soft delete)
  forceDelete: (id) => `/users/${id}/force`,     // DELETE permanent
  restore    : (id) => `/users/${id}/restore`,   // POST restore
}
