#!/usr/bin/env python

import os, time, smbus

"""
  Pour que i2c fonctionne, il ne faut pas que le kernel utilise le module.
  En bref, i2cdectect -y 1 0x77 doit afficher un 77 et non un UU.
"""

os.system('modprobe -r bmp280_i2c')
os.system('modprobe i2c-dev')
os.system('i2cdetect -y 1 0x77')
os.system('echo heartbeat > /sys/class/leds/led0/trigger')

#bus = smbus.SMBus(0)   # /dev/i2c-0
bus = smbus.SMBus(1)    # /dev/i2c-1 

UTreg = 0xF6
DEVICE = 0x77
CALIBreg = 0xAA

oss = 3 # oversampling_setting (mode)
        # 0 = ultra low power, 1 = standard, 2 = high, 3 = ultra high resolution

#--------------------------------------------------------------------

def toBinaire(numb):
    return str(numb) if numb<=1 else bin(numb>>1) + str(numb&1)

def calcREG(data, index):
    numb = (data[index] << 8) + data[index + 1]
    result = -(numb & 0x8000) | (numb & 0x7fff)
    return result

def getUT():
    bus.write_byte_data(DEVICE, 0xF4, 0x2E) # write 0x2E into reg 0xF4
    time.sleep(0.005)   # wait 5ms
    (msb, lsb) = bus.read_i2c_block_data(DEVICE, UTreg, 2)
    UT = (msb << 8) + lsb
    return UT

def getUP():
    bus.write_byte_data(DEVICE, 0xF4, 0x34 + (oss << 6))    # write 0x2E into reg 0xF4
    time.sleep(0.004)   # wait 4ms
    (msb, lsb, xlsb) = bus.read_i2c_block_data(DEVICE, UTreg, 3)
    UP = ((msb << 16) + (lsb << 8) + xlsb) >> (8 - oss)
    return UP

def calcB5(calib):
    AC5 = calcREG(calib, 8)
    AC6 = calcREG(calib, 10)
    MC  = calcREG(calib, 18)
    MD  = calcREG(calib, 20)
    UT  = getUT()
    
    """
    # Datasheet Values
    AC5 = 32757
    AC6 = 23153
    MC = -8711
    MD = 2868
    UT = 27898
    """

    X1 = ((UT - AC6) * AC5) >> 15
    X2 = (MC << 11) / (X1 + MD)
    B5 = X1 + X2
    return B5


def calcTemperature(calib):
    B5 = calcB5(calib)
    T = int(B5 + 8) >> 4
    return T

def calcPression(calib):
    AC1 = calcREG(calib, 0)
    AC2 = calcREG(calib, 2)
    AC3 = calcREG(calib, 4)
    AC4 = calcREG(calib, 6)
    B1  = calcREG(calib, 12)
    B2  = calcREG(calib, 14)
    UP  = getUP()
    
    """
    # Datasheet Values
    AC1 = 408
    AC2 = -72
    AC3 = -14383
    AC4 = 32741
    B1 = 6190
    B2 = 4
    UP = 23843
    """

    B5 = calcB5(calib)
    B6 = B5 - 4000
    B6b = int(B6 * B6) >> 12
    X1 = int(B6 * B6b) >> 11
    X2 = int(AC2 * B6) >> 11
    X3 = X1 + X2
    B3 = (((AC1 * 4 + X3) << oss) + 2) >> 2
    
    X1 = int(AC3 * B6) >> 13
    X2 = (B1 * B6b) >> 16
    X3 = ((X1 + X2) + 2) >> 2
    B4 = (AC4 * (X3 + 32768)) >> 15
    B7 = (UP - B3) * (50000 >> oss)
    
    if B7 < 0x80000000:
        P = (B7 * 2) / B4
    else:
        P = (B7 / B4) * 2
    
    X1 = (int(P) >> 8) * (int(P) >> 8)
    X1 = (X1 * 3038) >> 16
    X2 = int(-7357 * P) >> 16
    P = int(P + ((X1 + X2 + 3791) >> 4))
    return P

#--------------------------------------------------------------------

# Info BMP180
(chip_id, chip_version) = bus.read_i2c_block_data(DEVICE, 0xD0, 2)
print("Chip ID  : {0}".format(chip_id))
print("Version  : {0}".format(chip_version))

# Read Calibration Data from EEPROM
calib = bus.read_i2c_block_data(DEVICE, CALIBreg, 22)

time.sleep(20)

while(1):
    # Calculte T and P
    temperature = calcTemperature(calib) / 10.0 # °C
    pression = calcPression(calib) / 100.0      # mbar 
    date = time.strftime("%Y;%m;%d;%H;%M", time.gmtime()) 
    #print("La température est %d°C avec une pression de %d hPa" % (temperature, pression))

    # Write in Database
    filePath = "/data/DataBase.csv"
    if os.path.exists(filePath):
        data = open(filePath, "a")
    else:
        data = open(filePath, "w")
    data.write("%s;%d;%d;\n" % (date, temperature, pression))
    data.close()

    time.sleep(5)
