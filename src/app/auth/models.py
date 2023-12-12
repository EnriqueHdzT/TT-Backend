from django.db import models

class userData(models.Model):
    GENDERS = (
        (0, "Male"),
        (1, "Female"),
        (2, "Other"),
    )

    firstLastName = models.CharField(max_length=128)
    secondLastName = models.CharField(max_length=128)
    name = models.CharField(max_length=128)
    dateOfBirth = models.DateField(null=True, default=None)
    gender = models.PositiveSmallIntegerField(choices=GENDERS, default=2)
    studentId = models.CharField(max_length=10)
    career = models.CharField(max_length=128)
    email = models.EmailField()
    phoneNumber = models.SmallIntegerField(unique=True)

    def __str__(self):
        return self.name

class credentials(models.Model):
    email = models.EmailField(primary_key=True)
    password = models.CharField(max_length=128)
    user = models.OneToOneField(userData, on_delete=models.CASCADE, unique=True)

    def __str__(self):
        return self.email
