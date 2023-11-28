from django.db import models

class userData(models.Model):
    email = models.EmailField()
    first_name = models.CharField(max_length=128)
    last_name = models.CharField(max_length=128)
    phone_number = models.SmallIntegerField(unique=True)

    def __str__(self):
        return self.email


class credentials(models.Model):
    email = models.EmailField(primary_key=True)
    password = models.CharField(max_length=128)
    user = models.OneToOneField(userData, on_delete=models.CASCADE, unique=True)

    def __str__(self):
        return self.email
