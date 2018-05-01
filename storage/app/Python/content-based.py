import sys
import numpy as np
import requests
import json

user_id = sys.argv[1]

r = requests.post('http://travelapp.test/api/getCBData', data={"user_id" : user_id})

hotel_features = r.json()['feature_vectors']
hotel_features = hotel_features.split('|')
hotel_features = [int(i) for i in hotel_features]
hotel_features = np.array(hotel_features).reshape(-1, 12)

ids = r.json()['hotel_ids']
ids = ids.split('|')
ids = [int(i) for i  in ids]
ids = np.array(ids)

scores = r.json()['ratings']
scores = scores.split('|')
scores = [float(i) for i in scores]
scores = np.array(scores)

from sklearn.feature_extraction.text import TfidfTransformer

transformer = TfidfTransformer(smooth_idf=True, norm ='l2')
tfidf = transformer.fit_transform(hotel_features.tolist()).toarray()

from sklearn.linear_model import Ridge
from sklearn import linear_model

d = tfidf.shape[1] # data dimension
W = np.zeros((d, 1))
b = np.zeros((1, 1))

clf = Ridge(alpha=0.01, fit_intercept = True)

Xhat = tfidf[ids, :]

clf.fit(Xhat, scores) 
W[:, 0] = clf.coef_
b[0, 0] = clf.intercept_

Yhat = tfidf.dot(W) + b

print(Yhat.tolist())