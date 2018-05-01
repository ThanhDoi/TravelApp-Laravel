import pandas as pd
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity
from scipy import sparse
import requests
import sys
import json

class CF(object):
	def __init__(self, Y_data, user_id, k, dist_func = cosine_similarity, uuCF = 1):
		self.uuCF = uuCF # user-user (1) or item-item (0) CF
		self.Y_data = Y_data if uuCF else Y_data[:, [1, 0, 2]]
		self.user_id = user_id
		self.k = k
		self.dist_func = dist_func
		self.Ybar_data = None
		self.n_users = int(np.max(self.Y_data[:, 0])) + 1
		self.start_user = int(np.min(self.Y_data[:, 0]))
		self.n_items = int(np.max(self.Y_data[:, 1])) + 1

	def normalize_Y(self):
		users = self.Y_data[:, 0]
		self.Ybar_data = self.Y_data.copy()
		self.mu = np.zeros((self.n_users,))
		for n in range(self.start_user, self.n_users):
			ids = np.where(users == n)[0].astype(np.int32)
			item_ids = self.Y_data[ids, 1]
			ratings = self.Y_data[ids, 2]
			m = np.mean(ratings)
			if np.isnan(m):
				m = 0
			self.mu[n] = m
			self.Ybar_data[ids, 2] = ratings - self.mu[n]
			self.Ybar = sparse.coo_matrix((self.Ybar_data[:, 2], (self.Ybar_data[:, 1], self.Ybar_data[:, 0])), (self.n_items, self.n_users))
			self.Ybar = self.Ybar.tocsr()

	def similarity(self):
		self.S = self.dist_func(self.Ybar.T, self.Ybar.T)

	def refresh(self):
		self.normalize_Y()
		self.similarity()

	def fit(self):
		self.refresh()

	def __pred(self, u, i, normalized = 1):
		ids = np.where(self.Y_data[:, 1] == i)[0].astype(np.int32)
		users_rated_i = (self.Y_data[ids, 0]).astype(np.int32)
		sim = self.S[u, users_rated_i]
		a = np.argsort(sim)[-self.k:]
		nearest_s = sim[a]
		r = self.Ybar[i, users_rated_i[a]]

		if normalized:
			return (r*nearest_s)[0]/(np.abs(nearest_s).sum() + 1e-8)

		return (r*nearest_s)[0]/(np.abs(nearest_s).sum() + 1e-8) + self.mu[u]

	def pred(self, u, i, normalized = 1):
		if self.uuCF: return self.__pred(u, i, normalized)
		return self.__pred(i, u, normalized)

	def recommend(self, u, normalized = 1):
		ids = np.where(self.Y_data[:, 0] == u)[0]
		items_rated_by_u = self.Y_data[ids, 1].tolist()
		recommended_items = []
		recommended_ratings = []
		for i in range(self.n_items):
			if i not in items_rated_by_u:
				rating = self.__pred(u, i)
				if rating > 0:
					recommended_items.append(i)
					recommended_ratings.append(rating)

		return (recommended_items, recommended_ratings)

	def print_recommendation(self):
		for u in range(self.user_id, self.user_id + 1):
			(recommended_items, recommended_ratings) = self.recommend(u)
			if self.uuCF:
				result = dict(zip(recommended_items, recommended_ratings))
				print(json.dumps(result))
			else:
				result = dict(zip(recommended_items, recommended_ratings))
				print(json.dumps(result))

user_id = int(sys.argv[1])

r = requests.post('http://travelapp.test/api/getCFData')

user_ids = r.json()['users']
user_ids = user_ids.split('|')
user_ids = [int(i) for i in user_ids]
user_ids = np.array(user_ids).reshape(-1, 1)

hotel_ids = r.json()['hotels']
hotel_ids = hotel_ids.split('|')
hotel_ids = [int(i) for i in hotel_ids]
hotel_ids = np.array(hotel_ids).reshape(-1, 1)

ratings = r.json()['ratings']
ratings = ratings.split('|')
ratings = [int(i) for i in ratings]
ratings = np.array(ratings).reshape(-1, 1)

Y_data = np.c_[user_ids, hotel_ids, ratings]
rs = CF(Y_data, user_id, k = 2, uuCF = 1)
rs.fit()

rs.print_recommendation()